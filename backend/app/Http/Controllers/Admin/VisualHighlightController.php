<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisualHighlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisualHighlightController extends Controller
{
    public function index()
    {
        $visualHighlights = VisualHighlight::query()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Visual highlights retrieved successfully.',
            'data' => $visualHighlights,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules(), $this->messages());

        $visualHighlight = VisualHighlight::query()->create([
            'image_path' => $request->file('image')->store('visual-highlights', VisualHighlight::PUBLIC_DISK),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
        ]);

        return response()->json([
            'message' => 'Visual highlight created successfully.',
            'data' => $visualHighlight,
        ], 201);
    }

    public function update(Request $request, VisualHighlight $visualHighlight)
    {
        $validated = $request->validate($this->updateRules(), $this->messages());
        $attributes = [];

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('visual-highlights', VisualHighlight::PUBLIC_DISK);
            $oldImagePath = $visualHighlight->image_path;

            $attributes['image_path'] = $newImagePath;

            if ($oldImagePath) {
                Storage::disk(VisualHighlight::PUBLIC_DISK)->delete($oldImagePath);
            }
        }

        if (array_key_exists('is_active', $validated)) {
            $attributes['is_active'] = (bool) $validated['is_active'];
        }

        $visualHighlight->update($attributes);

        return response()->json([
            'message' => 'Visual highlight updated successfully.',
            'data' => $visualHighlight->fresh(),
        ]);
    }

    public function destroy(VisualHighlight $visualHighlight)
    {
        $visualHighlight->delete();

        return response()->json([
            'message' => 'Visual highlight removed successfully.',
        ]);
    }

    private function storeRules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'image.required' => 'An image is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Images must be a JPG, JPEG, PNG, or WEBP file.',
            'image.max' => 'Images must not be larger than 4 MB.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }
}
