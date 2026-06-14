<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreInspectionQuotationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'system_type',
        'with_battery',
        'system_kw',
        'panel_quantity',
        'panel_watts',
        'base_project_cost',
        'inverter_capacity_kw',
        'inverter_cost',
        'battery_required_kwh',
        'battery_required_ah',
        'battery_capacity_ah',
        'battery_voltage',
        'battery_cost',
        'project_cost',
        'estimated_monthly_savings',
        'estimated_annual_savings',
        'roi_years',
        'requires_technician_validation',
        'validation_note',
    ];

    protected function casts(): array
    {
        return [
            'with_battery' => 'boolean',
            'system_kw' => 'decimal:2',
            'panel_watts' => 'decimal:2',
            'base_project_cost' => 'decimal:2',
            'inverter_capacity_kw' => 'decimal:2',
            'inverter_cost' => 'decimal:2',
            'battery_required_kwh' => 'decimal:2',
            'battery_required_ah' => 'decimal:2',
            'battery_capacity_ah' => 'decimal:2',
            'battery_voltage' => 'decimal:2',
            'battery_cost' => 'decimal:2',
            'project_cost' => 'decimal:2',
            'estimated_monthly_savings' => 'decimal:2',
            'estimated_annual_savings' => 'decimal:2',
            'roi_years' => 'decimal:2',
            'requires_technician_validation' => 'boolean',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
