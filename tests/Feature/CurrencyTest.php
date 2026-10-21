<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Services\InvoicingService;
use App\Services\ServiceOrderService;
use Database\Seeders\TariffSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create());
    }

    public function test_currency_is_per_tariff_and_not_mixed(): void
    {
        $this->seed(TariffSeeder::class);
        $client = Client::firstOrFail();
        $mxn = Service::where('code', 'SOP-HR')->first();
        $usd = Service::where('code', 'DEV-HR')->first();
        $usd->tariffLists()->updateExistingPivot($client->tariff_list_id, ['currency' => 'USD', 'price' => 40]);

        $svc = app(ServiceOrderService::class);
        $o1 = $svc->create(['client_id' => $client->id, 'title' => 'A', 'items' => [['service_id' => $mxn->id, 'quantity' => 1]]]);
        $o2 = $svc->create(['client_id' => $client->id, 'title' => 'B', 'items' => [['service_id' => $usd->id, 'quantity' => 2]]]);
        $this->assertSame('MXN', $o1->fresh()->currency);
        $this->assertSame('USD', $o2->fresh()->currency);

        try {
            $svc->addItem($o1, $usd->id, 1);
            $this->fail('DebiÃ³ rechazar monedas mezcladas en una orden.');
        } catch (DomainException) {
        }

        $svc->changeStatus($o1, ServiceOrder::COMPLETED);
        $svc->changeStatus($o2, ServiceOrder::COMPLETED);

        try {
            app(InvoicingService::class)->createFromOrders($client, [$o1->id, $o2->id]);
            $this->fail('DebiÃ³ rechazar facturar monedas mezcladas.');
        } catch (DomainException) {
        }

        $invoice = app(InvoicingService::class)->createFromOrders($client, [$o2->id]);
        $this->assertSame('USD', $invoice->currency);
        $this->assertEquals(80, (float) $invoice->subtotal);
        $this->get(route('invoices.pdf', $invoice))->assertOk();
        $this->get(route('dashboard'))->assertOk();
    }
}
