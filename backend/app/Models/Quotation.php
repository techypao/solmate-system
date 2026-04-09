<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quotation_type',
        'monthly_electric_bill',
        'rate_per_kwh',
        'days_in_month',
        'sun_hours',
        'pv_safety_factor',
        'battery_factor',
        'battery_voltage',
        'pv_system_type',
        'with_battery',
        'inverter_type',
        'battery_model',
        'battery_capacity_ah',
        'panel_watts',
        'monthly_kwh',
        'daily_kwh',
        'pv_kw_raw',
        'pv_kw_safe',
        'panel_quantity',
        'system_kw',
        'battery_required_kwh',
        'battery_required_ah',
        'panel_cost',
        'inverter_cost',
        'battery_cost',
        'bos_cost',
        'materials_subtotal',
        'labor_cost',
        'project_cost',

        'estimated_monthly_savings',
    'estimated_annual_savings',
    'roi_years',
    
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}