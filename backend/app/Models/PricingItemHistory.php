<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingItemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'pricing_item_id',
        'performed_by_id',
        'performed_by_snapshot',
        'action',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'performed_by_snapshot' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function pricingItem(): BelongsTo
    {
        return $this->belongsTo(PricingItem::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_id');
    }
}
