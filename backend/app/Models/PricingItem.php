<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingItem extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'panel',
        'inverter',
        'battery',
        'protection',
        'mounting',
        'wiring',
        'grounding',
        'misc',
    ];

    protected $fillable = [
        'name',
        'category',
        'unit',
        'default_unit_price',
        'brand',
        'model',
        'specification',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function quotationLineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class);
    }
}
