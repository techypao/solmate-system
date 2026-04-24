<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\InAppNotificationService;
use App\Services\PreferredDateLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    public function __construct(
        private InAppNotificationService $notificationService,
        private PreferredDateLockService $preferredDateLockService
    ) {}

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
            'address' => 'nullable|string|max:255',
            'date_needed' => 'nullable|date',
        ]);

        $userAddress = trim((string) ($request->user()->address ?? ''));
        $providedAddress = trim((string) ($validated['address'] ?? ''));
        $resolvedAddress = $providedAddress !== '' ? $providedAddress : ($userAddress !== '' ? $userAddress : null);

        $serviceRequest = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed'] ?? null],
            function () use ($request, $validated, $resolvedAddress) {
                return DB::transaction(function () use ($request, $validated, $resolvedAddress) {
                    $this->preferredDateLockService->ensureDateIsAvailable($validated['date_needed'] ?? null);

                    return ServiceRequest::query()->create([
                        'user_id' => $request->user()->id,
                        'request_type' => $validated['request_type'],
                        'details' => $validated['details'],
                        'contact_number' => trim($validated['contact_number']),
                        'address' => $resolvedAddress,
                        'date_needed' => $validated['date_needed'] ?? null,
                        'status' => 'pending',
                    ]);
                });
            }
        );

        $serviceRequest->load('customer');
        $this->notificationService->notifyAdminsOfNewServiceRequest($serviceRequest, $request->user());

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
        $previousTechnicianId = $serviceRequest->technician_id;
        $previousStatus = $serviceRequest->status;

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

        if ($previousTechnicianId !== $technician->id) {
            $this->notificationService->notifyTechnicianOfServiceRequestAssignment(
                $serviceRequest,
                $request->user()->id
            );
        }

        if ($previousStatus !== $serviceRequest->status) {
            $this->notificationService->notifyCustomerOfServiceRequestStatusUpdate(
                $serviceRequest,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Technician assigned successfully.',
            'data' => $serviceRequest,
        ], 200);
    }

    public function updatePreferredDate(Request $request, $id)
    {
        $validated = $request->validate([
            'date_needed' => 'required|date',
        ], [
            'date_needed.required' => 'Preferred date is required.',
            'date_needed.date' => 'Preferred date must be a valid date.',
        ]);

        $currentDate = ServiceRequest::query()
            ->findOrFail($id)
            ->date_needed;

        $result = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed'], $currentDate],
            function () use ($id, $validated) {
                return DB::transaction(function () use ($id, $validated) {
                    $serviceRequest = ServiceRequest::query()
                        ->with(['customer', 'technician'])
                        ->lockForUpdate()
                        ->findOrFail($id);

                    $this->preferredDateLockService->ensureDateIsAvailable(
                        $validated['date_needed'],
                        $serviceRequest->id,
                        ServiceRequest::class
                    );

                    $previousDate = $serviceRequest->date_needed?->toDateString();
                    $serviceRequest->date_needed = $validated['date_needed'];
                    $serviceRequest->save();

                    return [
                        'service_request' => $serviceRequest->fresh(['customer', 'technician']),
                        'previous_date' => $previousDate,
                    ];
                });
            }
        );

        $serviceRequest = $result['service_request'];
        $previousDate = $result['previous_date'];

        if ($previousDate !== $validated['date_needed']) {
            $this->notificationService->notifyServiceRequestRescheduled(
                $serviceRequest,
                $previousDate,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Service preferred date updated successfully.',
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

        if (! in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            return response()->json([
                'message' => "Invalid status transition from {$currentStatus} to {$newStatus}.",
            ], 422);
        }

        $serviceRequest->status = $newStatus;
        $serviceRequest->save();

        if ($currentStatus !== $newStatus) {
            $this->notificationService->notifyCustomerOfServiceRequestStatusUpdate(
                $serviceRequest,
                $request->user()->id
            );
        }

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
            'status' => 'required|in:pending,approved,scheduled,assigned,in_progress,cancelled,declined,completed',
        ]);

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);
        $previousStatus = $serviceRequest->status;

        $serviceRequest->status = $request->status;
        $serviceRequest->save();

        if ($previousStatus !== $serviceRequest->status) {
            $this->notificationService->notifyCustomerOfServiceRequestStatusUpdate(
                $serviceRequest,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Official service request status updated successfully.',
            'data' => $serviceRequest,
        ], 200);
    }
}
