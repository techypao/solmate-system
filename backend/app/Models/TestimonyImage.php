<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TestimonyImage extends Model
{
    use HasFactory;

    public const PUBLIC_DISK = 'public';

    protected $fillable = [
        'testimony_id',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $testimonyImage): void {
            if ($testimonyImage->image_path) {
                Storage::disk(self::PUBLIC_DISK)->delete($testimonyImage->image_path);
            }
        });
    }

    public function testimony(): BelongsTo
    {
        return $this->belongsTo(Testimony::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk(self::PUBLIC_DISK)->url($this->image_path);
    }
}
