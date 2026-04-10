<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $serviceRequests = ServiceRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($serviceRequests, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|string|max:255',
            'details' => 'required|string',
            'date_needed' => 'nullable|date',
        ]);

        $serviceRequest = ServiceRequest::create([
            'user_id' => $request->user()->id,
            'request_type' => $validated['request_type'],
            'details' => $validated['details'],
            'date_needed' => $validated['date_needed'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Service request submitted successfully.',
            'data' => $serviceRequest
        ], 201);

        
    $serviceRequests = ServiceRequest::where('user_id', $request->user()->id)
        ->latest()
        ->get();

    return response()->json($serviceRequests);
}
    public function assignTechnician(Request $request, $id)
{
    $request->validate([
        'technician_id' => 'required|exists:users,id',
    ]);

    $serviceRequest = ServiceRequest::findOrFail($id);
    $technician = User::findOrFail($request->technician_id);

    if ($technician->role !== 'technician') {
        return response()->json([
            'message' => 'Selected user is not a technician.'
        ], 422);
    }

    $serviceRequest->technician_id = $technician->id;
    $serviceRequest->status = 'assigned';
    $serviceRequest->save();

    $serviceRequest->load(['customer', 'technician']);

    return response()->json([
        'message' => 'Technician assigned successfully.',
        'data' => $serviceRequest
    ], 200);
}
}

    
