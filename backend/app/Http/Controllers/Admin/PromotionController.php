<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    public function __construct(private readonly InAppNotificationService $notifications) {}

    public function index()
    {
        return response()->json([
            'message' => 'Promotions retrieved successfully.',
            'data' => Promotion::query()->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules(), $this->messages());

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('promotions', Promotion::PUBLIC_DISK);
        }

        $promotion = Promotion::query()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : true,
            'promo_type' => $validated['promo_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? null,
            'free_item_description' => $validated['free_item_description'] ?? null,
            'conditions' => $this->sanitizeConditions($validated['conditions'] ?? null, $validated['promo_type'] ?? null),
        ]);

        if ($promotion->is_active) {
            $this->notifications->notifyAllCustomersOfNewPromotion($promotion, $request->user()?->id);
        }

        return response()->json([
            'message' => 'Promotion created successfully.',
            'data' => $promotion->fresh(),
        ], 201);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate($this->updateRules(), $this->messages());
        $attributes = [];

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('promotions', Promotion::PUBLIC_DISK);
            $oldImagePath = $promotion->image_path;
            $attributes['image_path'] = $newImagePath;
            if ($oldImagePath) {
                Storage::disk(Promotion::PUBLIC_DISK)->delete($oldImagePath);
            }
        }

        foreach (['title', 'description', 'start_date', 'end_date', 'promo_type', 'free_item_description'] as $field) {
            if (array_key_exists($field, $validated)) {
                $attributes[$field] = $validated[$field];
            }
        }

        if (array_key_exists('discount_value', $validated)) {
            $attributes['discount_value'] = $validated['discount_value'];
        }

        if (array_key_exists('conditions', $validated)) {
            $attributes['conditions'] = $this->sanitizeConditions(
                $validated['conditions'],
                $validated['promo_type'] ?? $promotion->promo_type
            );
        }

        if (array_key_exists('is_active', $validated)) {
            $attributes['is_active'] = (bool) $validated['is_active'];
        }

        $promotion->update($attributes);

        return response()->json([
            'message' => 'Promotion updated successfully.',
            'data' => $promotion->fresh(),
        ]);
    }

    public function toggle(Promotion $promotion)
    {
        $promotion->update(['is_active' => ! $promotion->is_active]);

        return response()->json([
            'message' => $promotion->is_active
                ? 'Promotion is now active.'
                : 'Promotion is now inactive.',
            'data' => $promotion->fresh(),
        ]);
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return response()->json([
            'message' => 'Promotion deleted successfully.',
        ]);
    }

    private function storeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'promo_type' => ['nullable', 'string', 'in:percentage,fixed_amount,free_item,bundle'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'free_item_description' => ['nullable', 'string', 'max:255'],
            'conditions' => ['nullable', 'array'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'promo_type' => ['nullable', 'string', 'in:percentage,fixed_amount,free_item,bundle'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'free_item_description' => ['nullable', 'string', 'max:255'],
            'conditions' => ['nullable', 'array'],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'A promo title is required.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image may not exceed 4 MB.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }

    /**
     * Keep only meaningful condition values; return null for non-free_item types
     * or when no applies_to is set (the key field for item-based conditions).
     */
    private function sanitizeConditions(?array $conditions, ?string $promoType): ?array
    {
        if ($promoType !== 'free_item' || !is_array($conditions)) {
            return null;
        }

        $appliesTo = trim((string) ($conditions['applies_to'] ?? ''));
        if ($appliesTo === '') {
            return null;
        }

        $result = ['applies_to' => $appliesTo];

        if (isset($conditions['min_qty']) && $conditions['min_qty'] !== '') {
            $result['min_qty'] = max(1, (int) $conditions['min_qty']);
        }

        if (isset($conditions['free_qty']) && $conditions['free_qty'] !== '') {
            $result['free_qty'] = max(1, (int) $conditions['free_qty']);
        }

        return $result;
    }
}
