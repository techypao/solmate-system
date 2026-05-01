<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VisualHighlight extends Model
{
    use HasFactory;

    public const PUBLIC_DISK = 'public';

    protected $fillable = [
        'image_path',
        'is_active',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $visualHighlight): void {
            if ($visualHighlight->image_path) {
                Storage::disk(self::PUBLIC_DISK)->delete($visualHighlight->image_path);
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk(self::PUBLIC_DISK)->url($this->image_path);
    }
}
