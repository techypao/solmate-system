<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequestOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceRequestOptionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', Rule::in(ServiceRequestOption::CATEGORIES)],
        ]);

        $options = ServiceRequestOption::query()
            ->when(
                $validated['category'] ?? null,
                fn ($query, string $category) => $query->where('category', $category)
            )
            ->ordered()
            ->get();

        return response()->json([
            'message' => 'Service request options retrieved successfully.',
            'data' => $options,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $this->ensureUniqueLabel($validated['category'], $validated['label']);

        $option = ServiceRequestOption::query()->create($validated);

        return response()->json([
            'message' => 'Service request option created successfully.',
            'data' => $option,
        ], 201);
    }

    public function update(Request $request, ServiceRequestOption $serviceRequestOption)
    {
        $validated = $request->validate($this->rules(true), $this->messages());
        if (array_key_exists('sort_order', $validated)) {
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
        }
        $category = $validated['category'] ?? $serviceRequestOption->category;
        $label = $validated['label'] ?? $serviceRequestOption->label;
        $this->ensureUniqueLabel($category, $label, $serviceRequestOption->id);

        $serviceRequestOption->update($validated);

        return response()->json([
            'message' => 'Service request option updated successfully.',
            'data' => $serviceRequestOption->fresh(),
        ]);
    }

    public function destroy(ServiceRequestOption $serviceRequestOption)
    {
        $serviceRequestOption->delete();

        return response()->json([
            'message' => 'Service request option removed successfully.',
        ]);
    }

    private function rules(bool $updating = false): array
    {
        return [
            'category' => [$updating ? 'sometimes' : 'required', 'string', Rule::in(ServiceRequestOption::CATEGORIES)],
            'label' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'category.required' => 'Choose whether this option is for installation or maintenance.',
            'category.in' => 'Choose a valid service option category.',
            'label.required' => 'Option label is required.',
            'label.max' => 'Option label must not be greater than 255 characters.',
            'description.max' => 'Description must not be greater than 1000 characters.',
            'sort_order.integer' => 'Sort order must be a whole number.',
        ];
    }

    private function ensureUniqueLabel(string $category, string $label, ?int $ignoreId = null): void
    {
        $exists = ServiceRequestOption::query()
            ->where('category', $category)
            ->where('label', $label)
            ->when($ignoreId, fn ($query, int $id) => $query->whereKeyNot($id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'label' => ['This option already exists for the selected category.'],
            ]);
        }
    }
}
