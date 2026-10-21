<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Services\InvoicingService;
use App\Services\ServiceOrderService;
use Database\Seeders\TariffSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_accumulate_into_one_invoice_and_render_pdf(): void
    {
        $this->seed(TariffSeeder::class);
        $client = Client::firstOrFail();
        $svc = app(ServiceOrderService::class);
        $support = Service::where('code', 'SOP-HR')->first();

        $ids = [];
        foreach ([2, 3] as $hours) {
            $order = $svc->create([
                'client_id' => $client->id,
                'title' => 'Soporte',
                'items' => [['service_id' => $support->id, 'quantity' => $hours]],
                'observation' => 'Se revisó el servidor',
            ]);
            $svc->changeStatus($order, ServiceOrder::COMPLETED);
            $ids[] = $order->id;
        }

        $invoice = app(InvoicingService::class)->createFromOrders($client, $ids);

        // Corporativo: 500 * 0.85 = 425 por hora; 5 horas
        $this->assertEquals('2125.00', $invoice->subtotal);
        $this->assertEquals('2465.00', $invoice->total);
        $this->assertSame(2, $invoice->serviceOrders()->where('status', 'invoiced')->count());

        $this->get(route('invoices.pdf', $invoice))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->expectException(DomainException::class);
        app(InvoicingService::class)->createFromOrders($client, $ids);
    }
}
