<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceRequestOptionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', Rule::in(ServiceRequestOption::CATEGORIES)],
        ]);

        $options = ServiceRequestOption::query()
            ->active()
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
}
