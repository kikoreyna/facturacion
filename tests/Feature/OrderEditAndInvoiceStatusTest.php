<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\InvoicingService;
use App\Services\ServiceOrderService;
use Database\Seeders\TariffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEditAndInvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->seed(TariffSeeder::class);
    }

    private function makeOrder(): ServiceOrder
    {
        return app(ServiceOrderService::class)->create([
            'client_id' => Client::firstOrFail()->id,
            'title' => 'Soporte',
            'items' => [['service_id' => Service::where('code', 'SOP-HR')->first()->id, 'quantity' => 2]],
        ]);
    }

    public function test_order_can_be_edited_with_items(): void
    {
        $order = $this->makeOrder();
        $item = $order->items()->first();

        $this->get(route('service-orders.edit', $order))->assertOk();

        $this->put(route('service-orders.update', $order), [
            'title' => 'Soporte editado',
            'description' => 'Nueva descripción',
            'items' => [$item->id => ['quantity' => 4]],
        ])->assertRedirect(route('service-orders.show', $order));

        $this->assertSame('Soporte editado', $order->fresh()->title);
        $this->assertEquals('4.00', $item->fresh()->quantity);
        $this->assertEquals(round(4 * (float) $item->unit_price, 2), (float) $item->fresh()->subtotal);

        $this->put(route('service-orders.update', $order), [
            'title' => 'Soporte editado',
            'items' => [$item->id => ['quantity' => 4, 'remove' => 1]],
        ]);
        $this->assertSame(0, $order->items()->count());
    }

    public function test_invoiced_order_cannot_be_edited(): void
    {
        $order = $this->makeOrder();
        app(ServiceOrderService::class)->changeStatus($order, ServiceOrder::COMPLETED);
        app(InvoicingService::class)->createFromOrders(Client::firstOrFail(), [$order->id]);

        $this->put(route('service-orders.update', $order), ['title' => 'X'])->assertSessionHasErrors('items');
        $this->assertSame('Soporte', $order->fresh()->title);
    }

    public function test_invoice_can_be_marked_sent_then_paid(): void
    {
        $order = $this->makeOrder();
        app(ServiceOrderService::class)->changeStatus($order, ServiceOrder::COMPLETED);
        $invoice = app(InvoicingService::class)->createFromOrders(Client::firstOrFail(), [$order->id]);

        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('Marcar enviada a cobro');

        $this->patch(route('invoices.status', $invoice), ['status' => 'sent'])->assertSessionHasNoErrors();
        $invoice->refresh();
        $this->assertSame(Invoice::SENT, $invoice->status);
        $this->assertNotNull($invoice->sent_at);

        $this->patch(route('invoices.status', $invoice), ['status' => 'paid'])->assertSessionHasNoErrors();
        $invoice->refresh();
        $this->assertSame(Invoice::PAID, $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        $this->patch(route('invoices.status', $invoice), ['status' => 'sent'])->assertSessionHasErrors('status');
        $this->get(route('invoices.index'))->assertOk()->assertSee('Pagada');
    }
}
