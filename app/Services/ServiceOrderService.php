<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrderLog;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ServiceOrderService
{
    /**
     * @param  array{client_id:int,title:string,description?:?string,scheduled_at?:?string,user_id?:?int,items?:array<int,array{service_id:int,quantity:numeric,description?:?string}>,observation?:?string}  $data
     */
    public function create(array $data, ?User $author = null): ServiceOrder
    {
        return DB::transaction(function () use ($data, $author) {
            $order = ServiceOrder::create([
                'number' => ServiceOrder::nextNumber(),
                'client_id' => $data['client_id'],
                'user_id' => $data['user_id'] ?? $author?->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'status' => ServiceOrder::SCHEDULED,
            ]);

            foreach ($data['items'] ?? [] as $item) {
                $this->addItem($order, (int) $item['service_id'], (float) $item['quantity'], $item['description'] ?? null);
            }

            if (! empty($data['observation'])) {
                $this->addLog($order, $data['observation'], $author);
            }

            return $order;
        });
    }

    /** Resuelve la tarifa con el tabulador del cliente y guarda el precio como snapshot. */
    public function addItem(ServiceOrder $order, int $serviceId, float $quantity, ?string $description = null): ServiceOrderItem
    {
        $this->assertEditable($order);

        $client = Client::findOrFail($order->client_id);
        $tariff = Service::findOrFail($serviceId)->tariffFor($client->tariff_list_id);

        if ($tariff === null) {
            throw new DomainException("El servicio {$serviceId} no tiene tarifa en el tabulador del cliente.");
        }

        $existing = $order->items()->value('currency');
        if ($existing !== null && $existing !== $tariff->currency) {
            throw new DomainException("Esta orden se cobra en {$existing}; el servicio tiene tarifa en {$tariff->currency}. Crea otra orden para esa moneda.");
        }

        return $order->items()->create([
            'service_id' => $serviceId,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $tariff->price,
            'currency' => $tariff->currency,
            'subtotal' => round($quantity * (float) $tariff->price, 2),
        ]);
    }

    public function update(ServiceOrder $order, array $data): ServiceOrder
    {
        $this->assertEditable($order);

        $order->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        return $order;
    }

    public function updateItem(ServiceOrderItem $item, float $quantity, ?string $description = null): ServiceOrderItem
    {
        $this->assertEditable($item->serviceOrder);

        $item->update([
            'quantity' => $quantity,
            'description' => $description,
            'subtotal' => round($quantity * (float) $item->unit_price, 2),
        ]);

        return $item;
    }

    public function removeItem(ServiceOrderItem $item): void
    {
        $this->assertEditable($item->serviceOrder);

        $item->delete();
    }

    public function addLog(ServiceOrder $order, string $body, ?User $author = null, bool $showOnInvoice = false): ServiceOrderLog
    {
        return $order->logs()->create([
            'user_id' => $author?->id,
            'body' => $body,
            'show_on_invoice' => $showOnInvoice,
        ]);
    }

    public function changeStatus(ServiceOrder $order, string $status, ?User $author = null): ServiceOrder
    {
        if ($order->status === ServiceOrder::INVOICED) {
            throw new DomainException('Una orden facturada no puede cambiar de estado.');
        }

        $order->update([
            'status' => $status,
            'completed_at' => $status === ServiceOrder::COMPLETED ? now() : null,
        ]);

        $this->addLog($order, "Estado cambiado a: {$status}", $author);

        return $order;
    }

    private function assertEditable(ServiceOrder $order): void
    {
        if (in_array($order->status, [ServiceOrder::INVOICED, ServiceOrder::CANCELLED], true)) {
            throw new DomainException('La orden ya no admite modificaciones.');
        }
    }
}
