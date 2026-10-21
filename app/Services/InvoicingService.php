<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Support\Currency;
use DomainException;
use Illuminate\Support\Facades\DB;

class InvoicingService
{
    /**
     * Agrupa órdenes completadas y sin facturar de un cliente en una sola factura.
     *
     * @param  array<int,int>  $orderIds
     */
    public function createFromOrders(Client $client, array $orderIds, float $taxRate = 16.0, ?string $notes = null, ?int $dueDays = 30): Invoice
    {
        return DB::transaction(function () use ($client, $orderIds, $taxRate, $notes, $dueDays) {
            // lockForUpdate evita que dos usuarios facturen la misma orden a la vez
            $orders = ServiceOrder::query()
                ->where('client_id', $client->id)
                ->whereIn('id', $orderIds)
                ->pendingInvoice()
                ->lockForUpdate()
                ->with('items')
                ->get();

            if ($orders->isEmpty() || $orders->count() !== count(array_unique($orderIds))) {
                throw new DomainException('Algunas órdenes no están completadas, no pertenecen al cliente o ya fueron facturadas.');
            }

            $currencies = $orders->flatMap->items->pluck('currency')->unique();
            if ($currencies->count() > 1) {
                throw new DomainException('No se pueden mezclar monedas en una factura ('.$currencies->implode(', ').'). Factura cada moneda por separado.');
            }

            $subtotal = round((float) $orders->flatMap->items->sum('subtotal'), 2);
            $tax = round($subtotal * $taxRate / 100, 2);

            $invoice = Invoice::create([
                'number' => $this->nextNumber(),
                'client_id' => $client->id,
                'tariff_list_id' => $client->tariff_list_id,
                'currency' => $currencies->first() ?? Currency::DEFAULT,
                'issued_at' => now()->toDateString(),
                'due_at' => $dueDays !== null ? now()->addDays($dueDays)->toDateString() : null,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax' => $tax,
                'total' => round($subtotal + $tax, 2),
                'status' => 'issued',
                'notes' => $notes,
            ]);

            ServiceOrder::whereIn('id', $orders->pluck('id'))->update([
                'invoice_id' => $invoice->id,
                'status' => ServiceOrder::INVOICED,
            ]);

            return $invoice;
        });
    }

    private function nextNumber(): string
    {
        return 'FAC-'.now()->format('Y').'-'.str_pad((string) ((Invoice::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }
}
