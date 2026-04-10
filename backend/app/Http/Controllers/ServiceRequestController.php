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
public function assignedRequests(Request $request)
{
    $technician = $request->user();

    $serviceRequests = ServiceRequest::with(['customer', 'technician'])
        ->where('technician_id', $technician->id)
        ->latest()
        ->get();

    return response()->json([
        'message' => 'Assigned service requests retrieved successfully.',
        'data' => $serviceRequests
    ], 200);
}

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:assigned,in_progress,completed',
    ]);

    $technician = $request->user();

    $serviceRequest = ServiceRequest::with(['customer', 'technician'])->findOrFail($id);

    if ($serviceRequest->technician_id !== $technician->id) {
        return response()->json([
            'message' => 'You are not allowed to update this service request.'
        ], 403);
    }

    $allowedTransitions = [
        'assigned' => ['in_progress'],
        'in_progress' => ['completed'],
        'completed' => [],
    ];

    $currentStatus = $serviceRequest->status;
    $newStatus = $request->status;

    if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
        return response()->json([
            'message' => "Invalid status transition from {$currentStatus} to {$newStatus}."
        ], 422);
    }

    $serviceRequest->status = $newStatus;
    $serviceRequest->save();

    return response()->json([
        'message' => 'Service request status updated successfully.',
        'data' => $serviceRequest
    ], 200);
}
}

    
