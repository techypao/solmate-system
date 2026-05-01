<?php

namespace App\Http\Controllers;

use App\Models\VisualHighlight;

class VisualHighlightController extends Controller
{
    public function index()
    {
        $visualHighlights = VisualHighlight::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Visual highlights retrieved successfully.',
            'data' => $visualHighlights,
        ]);
    }
}
