<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\ServiceRequest;


class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'inspection_request_id',
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
    'applied_promo_id',
    'promo_discount',
    'discount_request_status',
    'discount_request_message',
    'discount_requested_at',
    'discount_request_resolved_at',
    'admin_discount_amount',
    'admin_discount_reason',
    'admin_discount_applied_by',
    'admin_discount_applied_at',
	];

    protected $appends = [
        'admin_discount_base_total',
        'has_admin_discount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function inspectionRequest(): BelongsTo
    {
        return $this->belongsTo(InspectionRequest::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class)->orderBy('id');
    }

    public function preInspectionOptions(): HasMany
    {
        return $this->hasMany(PreInspectionQuotationOption::class)->orderBy('id');
    }

    public function appliedPromo(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'applied_promo_id');
    }

    public function adminDiscountAppliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_discount_applied_by');
    }

    public function adminDiscountBaseTotal(): ?float
    {
        if ($this->materials_subtotal !== null || $this->labor_cost !== null) {
            return round(max(
                0,
                (float) ($this->materials_subtotal ?? 0)
                    + (float) ($this->labor_cost ?? 0)
                    - (float) ($this->promo_discount ?? 0)
            ), 2);
        }

        if ($this->project_cost === null) {
            return null;
        }

        return round((float) $this->project_cost + (float) ($this->admin_discount_amount ?? 0), 2);
    }

    public function getAdminDiscountBaseTotalAttribute(): ?float
    {
        return $this->adminDiscountBaseTotal();
    }

    public function getHasAdminDiscountAttribute(): bool
    {
        return (float) ($this->admin_discount_amount ?? 0) > 0;
    }

    protected function casts(): array
    {
        return [
            'promo_discount' => 'decimal:2',
            'admin_discount_amount' => 'decimal:2',
            'discount_requested_at' => 'datetime',
            'discount_request_resolved_at' => 'datetime',
            'admin_discount_applied_at' => 'datetime',
        ];
    }
}
