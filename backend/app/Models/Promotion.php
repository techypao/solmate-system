<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    use HasFactory;

    public const PUBLIC_DISK = 'public';

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $promotion): void {
            if ($promotion->image_path) {
                Storage::disk(self::PUBLIC_DISK)->delete($promotion->image_path);
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(self::PUBLIC_DISK);

        return $disk->url($this->image_path);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrentlyLive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->active()
            ->where(function (Builder $q) use ($today): void {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function (Builder $q) use ($today): void {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            });
    }
}
