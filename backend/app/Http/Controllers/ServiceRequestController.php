<?php

namespace App\Http\Controllers;

use App\Models\CompletionReport;
use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\CustomerRequestEligibilityService;
use App\Services\InAppNotificationService;
use App\Services\PreferredDateLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function __construct(
        private CustomerRequestEligibilityService $customerRequestEligibilityService,
        private InAppNotificationService $notificationService,
        private PreferredDateLockService $preferredDateLockService
    ) {}

    public function index(Request $request)
    {
        $serviceRequests = ServiceRequest::query()
            ->with(['technician', 'completionReport.technician', 'completionReport.approver'])
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
            'address' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($request) {
                    $requestType = strtolower((string) $request->input('request_type'));

                    return in_array($requestType, ['installation', 'maintenance'], true);
                }),
            ],
            'address_details' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'date_needed' => 'nullable|date',
        ]);

        $userAddress = trim((string) ($request->user()->address ?? ''));
        $providedAddress = trim((string) ($validated['address'] ?? ''));
        $resolvedAddress = $providedAddress !== '' ? $providedAddress : ($userAddress !== '' ? $userAddress : null);
        $resolvedAddressDetails = trim((string) ($validated['address_details'] ?? ''));

        $serviceRequest = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed'] ?? null],
            function () use ($request, $validated, $resolvedAddress, $resolvedAddressDetails) {
                return DB::transaction(function () use ($request, $validated, $resolvedAddress, $resolvedAddressDetails) {
                    $this->customerRequestEligibilityService->ensureCustomerCanCreateRequest($request->user()->id);

                    $this->preferredDateLockService->ensureDateIsAvailable(
                        $validated['date_needed'] ?? null,
                        null,
                        null,
                        strtolower((string) ($validated['request_type'] ?? ''))
                    );

                    return ServiceRequest::query()->create([
                        'user_id' => $request->user()->id,
                        'request_type' => $validated['request_type'],
                        'details' => $validated['details'],
                        'contact_number' => trim($validated['contact_number']),
                        'address' => $resolvedAddress,
                        'address_details' => $resolvedAddressDetails !== '' ? $resolvedAddressDetails : null,
                        'latitude' => $validated['latitude'] ?? null,
                        'longitude' => $validated['longitude'] ?? null,
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

    public function storeManualInspection(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:30',
            'address_details' => 'required|string|max:255',
            'details' => 'required|string',
            'date_needed' => 'required|date',
        ]);

        $inspectionRequest = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed']],
            function () use ($validated) {
                return DB::transaction(function () use ($validated) {
                    $this->preferredDateLockService->ensureDateIsAvailable(
                        $validated['date_needed'],
                        null,
                        null,
                        PreferredDateLockService::REQUEST_TYPE_INSPECTION
                    );

                    return InspectionRequest::query()->create([
                        'user_id' => null,
                        'customer_name' => trim($validated['customer_name']),
                        'customer_email' => trim($validated['customer_email']),
                        'details' => trim($validated['details']),
                        'contact_number' => trim($validated['contact_number']),
                        'address' => null,
                        'address_details' => trim($validated['address_details']),
                        'date_needed' => $validated['date_needed'],
                        'status' => 'pending',
                    ]);
                });
            }
        );

        return response()->json([
            'message' => 'Manual inspection request created successfully.',
            'data' => $inspectionRequest,
        ], 201);
    }

    public function assignTechnician(Request $request, int $id)
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
            $serviceRequest->completionReport()->delete();
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

    public function updatePreferredDate(Request $request, int $id)
    {
        $validated = $request->validate([
            'date_needed' => 'required|date',
            'bypass_reserved_date_lock' => 'sometimes|boolean',
        ], [
            'date_needed.required' => 'Preferred date is required.',
            'date_needed.date' => 'Preferred date must be a valid date.',
        ]);

        $bypassReservedDateLock = (bool) ($validated['bypass_reserved_date_lock'] ?? false);

        $currentRecord = ServiceRequest::query()->findOrFail($id);
        $currentDate = $currentRecord->date_needed;
        $recordRequestType = $currentRecord->isManualInspectionRequest()
            ? PreferredDateLockService::REQUEST_TYPE_INSPECTION
            : strtolower((string) $currentRecord->request_type);

        $result = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed'], $currentDate],
            function () use ($id, $validated, $bypassReservedDateLock, $recordRequestType) {
                return DB::transaction(function () use ($id, $validated, $bypassReservedDateLock, $recordRequestType) {
                    $serviceRequest = ServiceRequest::query()
                        ->with(['customer', 'technician'])
                        ->lockForUpdate()
                        ->findOrFail($id);

                    if (! $bypassReservedDateLock) {
                        $this->preferredDateLockService->ensureDateIsAvailable(
                            $validated['date_needed'],
                            $serviceRequest->id,
                            ServiceRequest::class,
                            $recordRequestType
                        );
                    }

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
            ->with(['customer', 'technician', 'completionReport.technician', 'completionReport.approver'])
            ->where('technician_id', $technician->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Assigned service requests retrieved successfully.',
            'data' => $serviceRequests,
        ], 200);
    }

    public function updateStatus(Request $request, int $id)
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
        return response()->json([
            'message' => 'Use the completion notes endpoint to request service completion approval.',
        ], 410);
    }

    public function cancelByCustomer(Request $request, int $id)
    {
        $validated = $request->validate([
            'cancellation_note' => 'required|string|min:5|max:1000',
        ]);

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician', 'completionReport.technician', 'completionReport.approver'])
            ->findOrFail($id);

        if ((int) $serviceRequest->user_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'You are not allowed to cancel this service request.',
            ], 403);
        }

        $currentStatus = strtolower((string) $serviceRequest->status);
        if (in_array($currentStatus, ['completed', 'cancelled', 'declined'], true)) {
            return response()->json([
                'message' => 'This service request can no longer be cancelled.',
            ], 422);
        }

        if ($serviceRequest->cancellation_note !== null) {
            return response()->json([
                'message' => 'A cancellation request has already been submitted for this service request.',
            ], 422);
        }

        $serviceRequest->cancellation_note = trim((string) $validated['cancellation_note']);
        $serviceRequest->save();

        $user = $request->user();
        $newCount = $user->incrementCancellationCount(performedByUserId: $user->id);

        $this->notificationService->notifyAdminsOfServiceRequestCancellation(
            $serviceRequest,
            $serviceRequest->cancellation_note,
            $user->id
        );

        return response()->json([
            'message' => 'Cancellation request submitted. The admin will review and update the status.',
            'data' => $serviceRequest,
            'cancellation_count' => $newCount,
            'account_archived' => $user->fresh()->isArchivedCustomer(),
        ], 200);
    }

    public function updateAdminStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,scheduled,assigned,in_progress,cancelled,declined,completed',
        ]);

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician', 'completionReport.technician', 'completionReport.approver'])
            ->findOrFail($id);
        $previousStatus = $serviceRequest->status;
        $nextStatus = $request->status;

        if ($nextStatus === 'completed') {
            $completionReport = $serviceRequest->completionReport;

            if (! $completionReport) {
                return response()->json([
                    'message' => 'Technician completion notes must be submitted before this service can be marked as completed.',
                ], 422);
            }

            if ($completionReport->status !== CompletionReport::STATUS_APPROVED) {
                $completionReport->status = CompletionReport::STATUS_APPROVED;
                $completionReport->approved_at = now();
                $completionReport->approved_by = $request->user()->id;
                $completionReport->save();
            }
        }

        $serviceRequest->status = $nextStatus;
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
