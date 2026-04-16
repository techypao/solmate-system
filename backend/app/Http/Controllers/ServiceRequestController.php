<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $serviceRequests = ServiceRequest::query()
            ->with('technician')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($serviceRequests, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|string|max:255',
            'details' => 'required|string',
            'contact_number' => 'required|string|max:30',
            'date_needed' => 'nullable|date',
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $request->user()->id,
            'request_type' => $validated['request_type'],
            'details' => $validated['details'],
            'contact_number' => trim($validated['contact_number']),
            'date_needed' => $validated['date_needed'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Service request submitted successfully.',
            'data' => $serviceRequest,
        ], 201);
    }

    public function assignTechnician(Request $request, $id)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $serviceRequest = ServiceRequest::query()->findOrFail($id);
        $technician = User::query()->findOrFail($request->technician_id);

        if ($technician->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Selected user is not a technician.',
            ], 422);
        }

        if ($serviceRequest->technician_id !== $technician->id) {
            $serviceRequest->technician_marked_done_at = null;
        }

        $serviceRequest->technician_id = $technician->id;
        $serviceRequest->status = 'assigned';
        $serviceRequest->save();

        $serviceRequest->load(['customer', 'technician']);

        return response()->json([
            'message' => 'Technician assigned successfully.',
            'data' => $serviceRequest,
        ], 200);
    }

    public function assignedRequests(Request $request)
    {
        $technician = $request->user();

        $serviceRequests = ServiceRequest::query()
            ->with(['customer', 'technician'])
            ->where('technician_id', $technician->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Assigned service requests retrieved successfully.',
            'data' => $serviceRequests,
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:assigned,in_progress',
        ]);

        $technician = $request->user();

        if ($technician->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Only technicians can update service request progress.',
            ], 403);
        }

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);

        if ($serviceRequest->technician_id !== $technician->id) {
            return response()->json([
                'message' => 'You are not allowed to update this service request.',
            ], 403);
        }

        $allowedTransitions = [
            'assigned' => ['in_progress'],
            'in_progress' => [],
            'completed' => [],
            'pending' => [],
        ];

        $currentStatus = $serviceRequest->status;
        $newStatus = $request->status;

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            return response()->json([
                'message' => "Invalid status transition from {$currentStatus} to {$newStatus}.",
            ], 422);
        }

        $serviceRequest->status = $newStatus;
        $serviceRequest->save();

        return response()->json([
            'message' => 'Service request progress updated successfully.',
            'data' => $serviceRequest,
        ], 200);
    }

    public function requestCompletion(Request $request, $id)
    {
        $technician = $request->user();

        if ($technician->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Only technicians can request service completion review.',
            ], 403);
        }

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);

        if ($serviceRequest->technician_id !== $technician->id) {
            return response()->json([
                'message' => 'You are not allowed to update this service request.',
            ], 403);
        }

        if ($serviceRequest->status !== 'in_progress') {
            return response()->json([
                'message' => 'Service completion can only be requested after the service is in progress.',
            ], 422);
        }

        if ($serviceRequest->technician_marked_done_at) {
            return response()->json([
                'message' => 'You already marked this service as done for admin review.',
            ], 422);
        }

        $serviceRequest->technician_marked_done_at = now();
        $serviceRequest->save();

        return response()->json([
            'message' => 'Service marked as done and sent for admin review.',
            'data' => $serviceRequest,
        ], 200);
    }

    public function updateAdminStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed',
        ]);

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);

        $serviceRequest->status = $request->status;
        $serviceRequest->save();

        return response()->json([
            'message' => 'Official service request status updated successfully.',
            'data' => $serviceRequest,
        ], 200);
    }
}
