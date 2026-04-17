<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionRequest;
use App\Models\User;
use App\Services\InAppNotificationService;

class InspectionRequestController extends Controller
{
    public function __construct(
        private InAppNotificationService $notificationService
    ) {
    }

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

        $inspectionRequest->load('customer');
        $this->notificationService->notifyAdminsOfNewInspectionRequest($inspectionRequest, $request->user());

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

        $inspectionRequest = InspectionRequest::query()->findOrFail($id);
        $technician = User::query()->findOrFail($request->technician_id);
        $previousTechnicianId = $inspectionRequest->technician_id;
        $previousStatus = $inspectionRequest->status;

        if ($technician->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Selected user is not a technician.'
            ], 422);
        }

        $inspectionRequest->technician_id = $request->technician_id;

        if ($inspectionRequest->status === 'pending') {
            $inspectionRequest->status = 'assigned';
        }

        $inspectionRequest->save();
        $inspectionRequest->load(['customer', 'technician']);

        if ($previousTechnicianId !== $technician->id) {
            $this->notificationService->notifyTechnicianOfInspectionRequestAssignment(
                $inspectionRequest,
                $request->user()->id
            );
        }

        if ($previousStatus !== $inspectionRequest->status) {
            $this->notificationService->notifyCustomerOfInspectionRequestStatusUpdate(
                $inspectionRequest,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Technician assigned successfully.',
            'inspection_request' => $inspectionRequest
        ]);
    }

    public function updatePreferredDate(Request $request, $id)
    {
        $validated = $request->validate([
            'date_needed' => 'required|date',
        ], [
            'date_needed.required' => 'Preferred date is required.',
            'date_needed.date' => 'Preferred date must be a valid date.',
        ]);

        $inspectionRequest = InspectionRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);
        $previousDate = $inspectionRequest->date_needed;
        $inspectionRequest->date_needed = $validated['date_needed'];
        $inspectionRequest->save();

        if ($previousDate !== $validated['date_needed']) {
            $this->notificationService->notifyInspectionRequestRescheduled(
                $inspectionRequest,
                $previousDate,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Inspection preferred date updated successfully.',
            'inspection_request' => $inspectionRequest->fresh(['customer', 'technician']),
        ]);
    }

    public function assignedToTechnician(Request $request)
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_TECHNICIAN) {
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

        if ($user->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Unauthorized. Only technicians can update inspection request status.'
            ], 403);
        }

        $inspectionRequest = InspectionRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);
        $previousStatus = $inspectionRequest->status;

        if ($inspectionRequest->technician_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You are not assigned to this inspection request.'
            ], 403);
        }

        $inspectionRequest->status = $request->status;
        $inspectionRequest->save();

        if ($previousStatus !== $inspectionRequest->status) {
            $this->notificationService->notifyCustomerOfInspectionRequestStatusUpdate(
                $inspectionRequest,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Inspection request status updated successfully.',
            'inspection_request' => $inspectionRequest
        ]);
    }
}
