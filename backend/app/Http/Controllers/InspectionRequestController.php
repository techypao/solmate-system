<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionRequest;

class InspectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $inspectionRequests = InspectionRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($inspectionRequests, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'details' => 'required|string',
            'date_needed' => 'nullable|date',
        ]);

        $inspectionRequest = InspectionRequest::create([
            'user_id' => $request->user()->id,
            'details' => $validated['details'],
            'date_needed' => $validated['date_needed'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Inspection request submitted successfully.',
            'data' => $inspectionRequest,
        ], 201);
    }
}