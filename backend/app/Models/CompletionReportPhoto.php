<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompletionReportPhoto extends Model
{
    use HasFactory;

    public const PUBLIC_DISK = 'public';

    protected $fillable = [
        'completion_report_id',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $photo): void {
            if ($photo->image_path) {
                Storage::disk(self::PUBLIC_DISK)->delete($photo->image_path);
            }
        });
    }

    public function completionReport(): BelongsTo
    {
        return $this->belongsTo(CompletionReport::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
        $disk = Storage::disk(self::PUBLIC_DISK);

        return $disk->url($this->image_path);
    }
}
