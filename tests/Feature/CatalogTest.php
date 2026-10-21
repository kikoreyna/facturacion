<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Service;
use App\Models\TariffList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create());
    }

    public function test_create_tariff_list_and_service_with_prices(): void
    {
        $this->post(route('tariff-lists.store'), ['name' => 'Gold', 'is_active' => 1])->assertRedirect(route('tariff-lists.index'));
        $list = TariffList::where('name', 'Gold')->firstOrFail();

        $this->get(route('services.create'))->assertOk()->assertSee('Gold');
        $this->post(route('services.store'), [
            'code' => 'X1', 'name' => 'Consultoría', 'unit' => 'hora', 'is_active' => 1,
            'prices' => [$list->id => '750.50'],
        ])->assertRedirect(route('services.index'));
        $service = Service::where('code', 'X1')->firstOrFail();
        $this->assertEqualsWithDelta(750.50, (float) $service->priceFor($list), 0.001);

        // Edición desde el tabulador: cambia precio y vacío elimina tarifa
        $this->get(route('tariff-lists.edit', $list))->assertOk()->assertSee('Consultoría');
        $this->put(route('tariff-lists.update', $list), ['name' => 'Gold', 'prices' => [$service->id => '800']])->assertRedirect();
        $this->assertEqualsWithDelta(800, (float) $service->priceFor($list), 0.001);
        $this->put(route('services.update', $service), ['code' => 'X1', 'name' => 'Consultoría', 'unit' => 'hora', 'prices' => [$list->id => '']])->assertRedirect();
        $this->assertNull($service->priceFor($list), 0.001);

        $this->get(route('services.index'))->assertOk();
        $this->get(route('tariff-lists.index'))->assertOk();
    }
}
