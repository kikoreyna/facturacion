<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceOrder;
use Database\Seeders\TariffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create());
    }

    public function test_full_ui_flow(): void
    {
        $this->seed(TariffSeeder::class);
        $tariff = Client::first()->tariff_list_id;

        $this->get(route('clients.create'))->assertOk();
        $this->post(route('clients.store'), ['name' => 'Acme', 'tariff_list_id' => $tariff, 'is_active' => 1])
            ->assertRedirect(route('clients.index'));
        $client = Client::where('name', 'Acme')->firstOrFail();
        $this->put(route('clients.update', $client), ['name' => 'Acme 2', 'tariff_list_id' => $tariff])->assertRedirect();

        $this->get(route('service-orders.create'))->assertOk();
        $service = Service::first();
        $this->post(route('service-orders.store'), [
            'client_id' => $client->id, 'title' => 'Prueba',
            'items' => [['service_id' => $service->id, 'quantity' => 2]], 'observation' => 'Inicio',
        ])->assertRedirect();
        $order = ServiceOrder::firstOrFail();
        $this->get(route('service-orders.show', $order))->assertOk()->assertSee('Inicio');
        $this->post(route('service-orders.logs.store', $order), ['body' => 'Avance'])->assertRedirect();
        $this->post(route('service-orders.items.store', $order), ['service_id' => $service->id, 'quantity' => 1])->assertRedirect();
        $this->patch(route('service-orders.status', $order), ['status' => 'completed'])->assertRedirect();

        $this->get(route('invoices.create', ['client_id' => $client->id]))->assertOk()->assertSee($order->number);
        $this->post(route('invoices.store'), ['client_id' => $client->id, 'order_ids' => [$order->id]])
            ->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee($invoice->number);
    }
}
