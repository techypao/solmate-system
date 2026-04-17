<?php

namespace App\Services;

use App\Models\Testimony;
use App\Models\TestimonyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TestimonyImageService
{
    public const MAX_IMAGES = 5;

    public function syncForStore(Testimony $testimony, array $uploadedImages = []): void
    {
        $uploadedImages = $this->normalizeUploadedImages($uploadedImages);

        if ($uploadedImages === []) {
            return;
        }

        $this->ensureImageLimit(0, count($uploadedImages));
        $this->storeImages($testimony, $uploadedImages);
    }

    public function syncForUpdate(
        Testimony $testimony,
        array $uploadedImages = [],
        array $removeImageIds = []
    ): void {
        $uploadedImages = $this->normalizeUploadedImages($uploadedImages);
        $removeImageIds = $this->normalizeIds($removeImageIds);

        DB::transaction(function () use ($testimony, $uploadedImages, $removeImageIds): void {
            $existingImages = $testimony->images()->get();

            if ($removeImageIds !== []) {
                $imagesToRemove = $existingImages->whereIn('id', $removeImageIds);

                if ($imagesToRemove->count() !== count($removeImageIds)) {
                    throw ValidationException::withMessages([
                        'remove_image_ids' => ['One or more selected images do not belong to this testimony.'],
                    ]);
                }

                $remainingImageCount = $existingImages->count() - $imagesToRemove->count();
                $this->ensureImageLimit($remainingImageCount, count($uploadedImages));

                $imagesToRemove->each->delete();
            } else {
                $this->ensureImageLimit($existingImages->count(), count($uploadedImages));
            }

            if ($uploadedImages !== []) {
                $this->storeImages($testimony, $uploadedImages);
            }
        });
    }

    private function storeImages(Testimony $testimony, array $uploadedImages): void
    {
        $storedPaths = [];

        try {
            foreach ($uploadedImages as $uploadedImage) {
                $path = $uploadedImage->store("testimonies/{$testimony->id}", TestimonyImage::PUBLIC_DISK);
                $storedPaths[] = $path;

                $testimony->images()->create([
                    'image_path' => $path,
                ]);
            }
        } catch (\Throwable $throwable) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk(TestimonyImage::PUBLIC_DISK)->delete($storedPath);
            }

            throw $throwable;
        }
    }

    private function ensureImageLimit(int $existingCount, int $newImageCount): void
    {
        if ($existingCount + $newImageCount > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => ['A testimony may only have up to '.self::MAX_IMAGES.' images.'],
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $uploadedImages
     * @return array<int, UploadedFile>
     */
    private function normalizeUploadedImages(array|UploadedFile|null $uploadedImages): array
    {
        if ($uploadedImages instanceof UploadedFile) {
            return [$uploadedImages];
        }

        return array_values(array_filter($uploadedImages ?? [], fn ($file) => $file instanceof UploadedFile));
    }

    /**
     * @param  array<int, int|string>|int|string|null  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array|int|string|null $ids): array
    {
        return collect(is_array($ids) ? $ids : [$ids])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
