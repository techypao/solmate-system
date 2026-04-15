<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationSetting extends Model
{
    protected $fillable = [
        'rate_per_kwh',
        'days_in_month',
        'sun_hours',
        'pv_safety_factor',
        'battery_factor',
        'battery_voltage',
        'labor_percentage',
        'default_bos_cost',
        'default_misc_cost',
        'default_panel_watts',
        'initial_price_per_kw',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_kwh' => 'decimal:2',
            'days_in_month' => 'integer',
            'sun_hours' => 'decimal:2',
            'pv_safety_factor' => 'decimal:2',
            'battery_factor' => 'decimal:2',
            'battery_voltage' => 'decimal:2',
            'labor_percentage' => 'decimal:2',
            'default_bos_cost' => 'decimal:2',
            'default_misc_cost' => 'decimal:2',
            'default_panel_watts' => 'decimal:2',
            'initial_price_per_kw' => 'decimal:2',
        ];
    }
}
