<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequestOption extends Model
{
    use HasFactory;

    public const CATEGORY_INSTALLATION_TYPE = 'installation_type';
    public const CATEGORY_MAINTENANCE_CONCERN = 'maintenance_concern';

    public const CATEGORIES = [
        self::CATEGORY_INSTALLATION_TYPE,
        self::CATEGORY_MAINTENANCE_CONCERN,
    ];

    protected $fillable = [
        'category',
        'label',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('label');
    }
}
