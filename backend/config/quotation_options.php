<?php

return [
    'initial_quotation' => [
        'default_system_type' => 'hybrid',
        'price_per_kw' => 50000.00,
    ],
    'final_quotation' => [
        'system_types' => [
            [
                'label' => 'Hybrid',
                'value' => 'hybrid',
            ],
            [
                'label' => 'On-Grid',
                'value' => 'on-grid',
            ],
            [
                'label' => 'Off-Grid',
                'value' => 'off-grid',
            ],
        ],
        'panel_options' => [
            [
                'label' => '550W Solar Panel',
                'value' => 550,
            ],
            [
                'label' => '610W Solar Panel',
                'value' => 610,
            ],
            [
                'label' => '650W Solar Panel',
                'value' => 650,
            ],
        ],
        'battery_options' => [
            [
                'label' => '51.2V 100Ah',
                'value' => '51.2V 100Ah',
                'battery_voltage' => 51.2,
                'battery_capacity_ah' => 100,
            ],
            [
                'label' => '51.2V 200Ah',
                'value' => '51.2V 200Ah',
                'battery_voltage' => 51.2,
                'battery_capacity_ah' => 200,
            ],
            [
                'label' => '51.2V 280Ah',
                'value' => '51.2V 280Ah',
                'battery_voltage' => 51.2,
                'battery_capacity_ah' => 280,
            ],
            [
                'label' => '51.2V 314Ah',
                'value' => '51.2V 314Ah',
                'battery_voltage' => 51.2,
                'battery_capacity_ah' => 314,
            ],
        ],
        'inverter_options' => [
            [
                'label' => '3kW Hybrid Inverter',
                'value' => '3kW Hybrid Inverter',
            ],
            [
                'label' => '5kW Hybrid Inverter',
                'value' => '5kW Hybrid Inverter',
            ],
            [
                'label' => '6kW Hybrid Inverter',
                'value' => '6kW Hybrid Inverter',
            ],
            [
                'label' => '8kW Hybrid Inverter',
                'value' => '8kW Hybrid Inverter',
            ],
        ],
    ],
];
