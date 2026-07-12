<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingItem;
use App\Models\PricingItemHistory;
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
            ->with(['histories.performedBy:id,name,email'])
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
        $this->recordHistory($pricingItem, $request, 'created', null, $this->snapshot($pricingItem));

        return response()->json([
            'message' => 'Pricing item created successfully.',
            'data' => $pricingItem->fresh(['histories.performedBy:id,name,email']),
        ], 201);
    }

    public function update(Request $request, PricingItem $pricingItem)
    {
        $validated = $request->validate($this->updateRules(), $this->messages());
        $oldValues = $this->snapshot($pricingItem);

        $pricingItem->update($validated);
        $freshPricingItem = $pricingItem->fresh();
        $this->recordHistory(
            $freshPricingItem,
            $request,
            $this->resolveUpdateAction($validated, $oldValues, $this->snapshot($freshPricingItem)),
            $oldValues,
            $this->snapshot($freshPricingItem)
        );

        return response()->json([
            'message' => 'Pricing item updated successfully.',
            'data' => $freshPricingItem->load(['histories.performedBy:id,name,email']),
        ]);
    }

    public function destroy(Request $request, PricingItem $pricingItem)
    {
        $this->recordHistory($pricingItem, $request, 'deleted', $this->snapshot($pricingItem), null);

        $pricingItem->delete();

        return response()->json([
            'message' => 'Pricing item removed successfully.',
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

    private function snapshot(PricingItem $pricingItem): array
    {
        return [
            'name' => $pricingItem->name,
            'category' => $pricingItem->category,
            'unit' => $pricingItem->unit,
            'default_unit_price' => $pricingItem->default_unit_price,
            'brand' => $pricingItem->brand,
            'model' => $pricingItem->model,
            'specification' => $pricingItem->specification,
            'is_active' => $pricingItem->is_active,
        ];
    }

    private function recordHistory(
        PricingItem $pricingItem,
        Request $request,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void {
        PricingItemHistory::query()->create([
            'pricing_item_id' => $pricingItem->id,
            'performed_by_id' => $request->user()?->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    private function resolveUpdateAction(array $validated, array $oldValues, array $newValues): string
    {
        if (array_keys($validated) === ['is_active'] && $oldValues['is_active'] !== $newValues['is_active']) {
            return $newValues['is_active'] ? 'activated' : 'deactivated';
        }

        return 'updated';
    }
}
