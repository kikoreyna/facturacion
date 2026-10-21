<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Service;
use App\Models\TariffList;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    public function run(): void
    {
        $lists = collect(['Estándar' => 1.0, 'Corporativo' => 0.85, 'Contrato Premium' => 0.7])
            ->map(fn ($factor, $name) => [TariffList::firstOrCreate(['name' => $name]), $factor]);

        $services = [
            ['SOP-HR', 'Soporte técnico', 'hora', 500],
            ['DEV-HR', 'Desarrollo de software', 'hora', 900],
            ['MNT-SRV', 'Mantenimiento preventivo', 'servicio', 1500],
        ];

        foreach ($services as [$code, $name, $unit, $base]) {
            $service = Service::firstOrCreate(['code' => $code], ['name' => $name, 'unit' => $unit]);

            foreach ($lists as [$list, $factor]) {
                $service->tariffLists()->syncWithoutDetaching([$list->id => ['price' => $base * $factor]]);
            }
        }

        Client::firstOrCreate(
            ['tax_id' => 'DEMO010101AAA'],
            ['name' => 'Empresa Demo S.A.', 'email' => 'demo@example.com', 'tariff_list_id' => $lists['Corporativo'][0]->id],
        );
    }
}
