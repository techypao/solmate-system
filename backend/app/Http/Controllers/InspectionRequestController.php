<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionRequest;
use App\Models\User;

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
            'contact_number' => 'required|string|max:30',
            'date_needed' => 'nullable|date',
        ]);

        $inspectionRequest = InspectionRequest::create([
            'user_id' => $request->user()->id,
            'details' => $validated['details'],
            'contact_number' => trim($validated['contact_number']),
            'date_needed' => $validated['date_needed'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Inspection request submitted successfully.',
            'data' => $inspectionRequest,
        ], 201);
    }

    public function assignTechnician(Request $request, $id)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $inspectionRequest = InspectionRequest::findOrFail($id);

        $technician = User::findOrFail($request->technician_id);

        if ($technician->role !== 'technician') {
            return response()->json([
                'message' => 'Selected user is not a technician.'
            ], 422);
        }

        $inspectionRequest->technician_id = $request->technician_id;

        if ($inspectionRequest->status === 'pending') {
            $inspectionRequest->status = 'assigned';
        }

        $inspectionRequest->save();

        return response()->json([
            'message' => 'Technician assigned successfully.',
            'inspection_request' => $inspectionRequest
        ]);
    }

    public function assignedToTechnician(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'technician') {
            return response()->json([
                'message' => 'Unauthorized. Only technicians can view assigned inspection requests.'
            ], 403);
        }

        $inspectionRequests = InspectionRequest::with('customer')
            ->where('technician_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'inspection_requests' => $inspectionRequests
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:assigned,in_progress,completed',
        ]);

        $user = $request->user();

        if ($user->role !== 'technician') {
            return response()->json([
                'message' => 'Unauthorized. Only technicians can update inspection request status.'
            ], 403);
        }

        $inspectionRequest = InspectionRequest::findOrFail($id);

        if ($inspectionRequest->technician_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You are not assigned to this inspection request.'
            ], 403);
        }

        $inspectionRequest->status = $request->status;
        $inspectionRequest->save();

        return response()->json([
            'message' => 'Inspection request status updated successfully.',
            'inspection_request' => $inspectionRequest
        ]);
    }
}
