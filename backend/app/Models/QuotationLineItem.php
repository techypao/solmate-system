<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'pricing_item_id',
        'description',
        'category',
        'qty',
        'unit',
        'unit_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function pricingItem(): BelongsTo
    {
        return $this->belongsTo(PricingItem::class);
    }
}
