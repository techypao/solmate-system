<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingItemController extends Controller
{
    public function catalog(Request $request)
    {
        abort_unless(
            in_array($request->user()?->role, [User::ROLE_ADMIN, User::ROLE_TECHNICIAN], true),
            403
        );

        $pricingItems = PricingItem::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Pricing catalog retrieved successfully.',
            'data' => $pricingItems,
        ]);
    }

    public function index()
    {
        $pricingItems = PricingItem::query()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Pricing items retrieved successfully.',
            'data' => $pricingItems,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules(), $this->messages());

        $pricingItem = PricingItem::query()->create($validated);

        return response()->json([
            'message' => 'Pricing item created successfully.',
            'data' => $pricingItem,
        ], 201);
    }

    public function update(Request $request, PricingItem $pricingItem)
    {
        $validated = $request->validate($this->updateRules(), $this->messages());

        $pricingItem->update($validated);

        return response()->json([
            'message' => 'Pricing item updated successfully.',
            'data' => $pricingItem->fresh(),
        ]);
    }

    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => ['required', 'string', Rule::in(PricingItem::CATEGORIES)],
            'unit' => 'required|string|max:50',
            'default_unit_price' => 'required|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'specification' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    private function updateRules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'category' => ['sometimes', 'string', Rule::in(PricingItem::CATEGORIES)],
            'unit' => 'sometimes|string|max:50',
            'default_unit_price' => 'sometimes|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'specification' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name must not be greater than 255 characters.',
            'category.required' => 'Category is required.',
            'category.string' => 'Category must be a valid string.',
            'category.in' => 'Category must be one of the supported pricing item categories.',
            'unit.required' => 'Unit is required.',
            'unit.string' => 'Unit must be a valid string.',
            'unit.max' => 'Unit must not be greater than 50 characters.',
            'default_unit_price.required' => 'Default unit price is required.',
            'default_unit_price.numeric' => 'Default unit price must be a valid number.',
            'default_unit_price.min' => 'Default unit price must be at least 0.',
            'brand.string' => 'Brand must be a valid string.',
            'brand.max' => 'Brand must not be greater than 255 characters.',
            'model.string' => 'Model must be a valid string.',
            'model.max' => 'Model must not be greater than 255 characters.',
            'specification.string' => 'Specification must be a valid string.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }
}
