<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create());
    }

    public function test_root_shows_dashboard_with_navbar(): void
    {
        $this->get('/')->assertOk()->assertSee('Dashboard')->assertSee('Facturas');
        $this->get(route('service-orders.index'))->assertOk();
        $this->get(route('invoices.index'))->assertOk();
    }
}
