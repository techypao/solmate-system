<?php

namespace Database\Seeders;

use App\Models\PricingItem;
use Illuminate\Database\Seeder;

class PricingCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->catalogItems() as $item) {
            PricingItem::query()->updateOrCreate(
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'unit' => $item['unit'],
                ],
                [
                    'default_unit_price' => $item['default_unit_price'],
                    'brand' => $item['brand'] ?? null,
                    'model' => $item['model'] ?? null,
                    'specification' => $item['specification'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function catalogItems(): array
    {
        return [
            [
                'name' => 'Canadian Mono 585W Bifacial',
                'category' => 'panel',
                'unit' => 'pc',
                'default_unit_price' => 4000,
                'brand' => 'Canadian',
                'model' => 'Mono 585W Bifacial',
            ],
            [
                'name' => 'OSDA Mono 610W Bifacial',
                'category' => 'panel',
                'unit' => 'pc',
                'default_unit_price' => 4550,
                'brand' => 'OSDA',
                'model' => 'Mono 610W Bifacial',
            ],
            [
                'name' => 'SRNE 6kW Hybrid',
                'category' => 'inverter',
                'unit' => 'pc',
                'default_unit_price' => 35000,
                'brand' => 'SRNE',
                'model' => '6kW Hybrid',
            ],
            [
                'name' => 'SRNE 51.2V 280Ah',
                'category' => 'battery',
                'unit' => 'pc',
                'default_unit_price' => 73000,
                'brand' => 'SRNE',
                'model' => '51.2V 280Ah',
            ],
            [
                'name' => 'FEEO AC 2P 40A',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 200,
                'brand' => 'FEEO',
                'model' => 'AC 2P 40A',
            ],
            [
                'name' => 'FEEO SPD AC 20-40KA 2P',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 430,
                'brand' => 'FEEO',
                'model' => 'SPD AC 20-40KA 2P',
            ],
            [
                'name' => 'FEEO DC 2P 550V 20A',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 400,
                'brand' => 'FEEO',
                'model' => 'DC 2P 550V 20A',
            ],
            [
                'name' => 'FEEO SPD DC 600V 2P',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 830,
                'brand' => 'FEEO',
                'model' => 'SPD DC 600V 2P',
            ],
            [
                'name' => 'FEEO ATS 2P 63A',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 1500,
                'brand' => 'FEEO',
                'model' => 'ATS 2P 63A',
            ],
            [
                'name' => 'BENY DC ISOLATOR 4P 32amps',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 1500,
                'brand' => 'BENY',
                'model' => 'DC ISOLATOR 4P 32amps',
            ],
            [
                'name' => 'IP65 18WAYS',
                'category' => 'protection',
                'unit' => 'pc',
                'default_unit_price' => 1520,
                'model' => '18WAYS',
            ],
            [
                'name' => 'RAILING 2.4m',
                'category' => 'mounting',
                'unit' => 'pc',
                'default_unit_price' => 632.5,
                'model' => '2.4m',
            ],
            [
                'name' => 'RAILING 4.8m',
                'category' => 'mounting',
                'unit' => 'pc',
                'default_unit_price' => 1100,
                'model' => '4.8m',
            ],
            [
                'name' => 'MID CLAMP 30mm',
                'category' => 'mounting',
                'unit' => 'pc',
                'default_unit_price' => 42,
                'model' => '30mm',
            ],
            [
                'name' => 'END CLAMP 30mm',
                'category' => 'mounting',
                'unit' => 'pc',
                'default_unit_price' => 42,
                'model' => '30mm',
            ],
            [
                'name' => 'LFOOT',
                'category' => 'mounting',
                'unit' => 'pc',
                'default_unit_price' => 77,
            ],
            [
                'name' => 'SPLICE KIT',
                'category' => 'mounting',
                'unit' => 'pc',
                'default_unit_price' => 79,
            ],
            [
                'name' => 'MC4',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 60,
            ],
            [
                'name' => 'MC4 with Fuse',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 225,
            ],
            [
                'name' => '3/4 Flexible Conduit',
                'category' => 'wiring',
                'unit' => 'm',
                'default_unit_price' => 110,
            ],
            [
                'name' => '3/4 Flexible Connector',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 60,
            ],
            [
                'name' => '1 inch Flexible Conduit',
                'category' => 'wiring',
                'unit' => 'm',
                'default_unit_price' => 160,
            ],
            [
                'name' => '1 inch Flexible Connector',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 80,
            ],
            [
                'name' => 'Cable Tray 80x80mm 2m',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 600,
            ],
            [
                'name' => 'Battery Cable 35mm Black',
                'category' => 'wiring',
                'unit' => 'm',
                'default_unit_price' => 320,
            ],
            [
                'name' => 'Battery Cable 35mm Red',
                'category' => 'wiring',
                'unit' => 'm',
                'default_unit_price' => 320,
            ],
            [
                'name' => 'Battery Lugs 35mm',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 20,
            ],
            [
                'name' => 'Copper Pin 10mm',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 20,
            ],
            [
                'name' => 'Cable Gland PG9',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 14,
            ],
            [
                'name' => 'Cable Gland PG11',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 20,
            ],
            [
                'name' => 'Junction Box 100x100x70mm',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 120,
            ],
            [
                'name' => 'MC4 Crimper',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 480,
            ],
            [
                'name' => 'MC4 Spanner',
                'category' => 'wiring',
                'unit' => 'pc',
                'default_unit_price' => 50,
            ],
            [
                'name' => 'Grounding Rod 3m',
                'category' => 'grounding',
                'unit' => 'pc',
                'default_unit_price' => 1170,
                'model' => '3m',
            ],
            [
                'name' => 'Grounding Lugs w/ clips',
                'category' => 'grounding',
                'unit' => 'pc',
                'default_unit_price' => 40,
            ],
            [
                'name' => 'Stainless 1 inch Clip',
                'category' => 'grounding',
                'unit' => 'pc',
                'default_unit_price' => 9,
            ],
        ];
    }
}
