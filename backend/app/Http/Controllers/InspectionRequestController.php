<?php

namespace App\Http\Controllers;

use App\Models\InspectionRequest;
use App\Models\User;
use App\Services\InAppNotificationService;
use App\Services\PreferredDateLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionRequestController extends Controller
{
    public function __construct(
        private InAppNotificationService $notificationService,
        private PreferredDateLockService $preferredDateLockService
    ) {}

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
            'address' => 'nullable|string|max:255',
            'date_needed' => 'nullable|date',
        ]);

        $userAddress = trim((string) ($request->user()->address ?? ''));
        $providedAddress = trim((string) ($validated['address'] ?? ''));
        $resolvedAddress = $providedAddress !== '' ? $providedAddress : ($userAddress !== '' ? $userAddress : null);

        $inspectionRequest = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed'] ?? null],
            function () use ($request, $validated, $resolvedAddress) {
                return DB::transaction(function () use ($request, $validated, $resolvedAddress) {
                    $this->preferredDateLockService->ensureDateIsAvailable($validated['date_needed'] ?? null);

                    return InspectionRequest::create([
                        'user_id' => $request->user()->id,
                        'details' => $validated['details'],
                        'contact_number' => trim($validated['contact_number']),
                        'address' => $resolvedAddress,
                        'date_needed' => $validated['date_needed'] ?? null,
                        'status' => 'pending',
                    ]);
                });
            }
        );

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
                'message' => 'Selected user is not a technician.',
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
            'inspection_request' => $inspectionRequest,
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

        $currentDate = InspectionRequest::query()
            ->findOrFail($id)
            ->date_needed;

        $result = $this->preferredDateLockService->withLockedDates(
            [$validated['date_needed'], $currentDate],
            function () use ($id, $validated) {
                return DB::transaction(function () use ($id, $validated) {
                    $inspectionRequest = InspectionRequest::query()
                        ->with(['customer', 'technician'])
                        ->lockForUpdate()
                        ->findOrFail($id);

                    $this->preferredDateLockService->ensureDateIsAvailable(
                        $validated['date_needed'],
                        $inspectionRequest->id,
                        InspectionRequest::class
                    );

                    $previousDate = $inspectionRequest->date_needed;
                    $inspectionRequest->date_needed = $validated['date_needed'];
                    $inspectionRequest->save();

                    return [
                        'inspection_request' => $inspectionRequest->fresh(['customer', 'technician']),
                        'previous_date' => $previousDate,
                    ];
                });
            }
        );

        $inspectionRequest = $result['inspection_request'];
        $previousDate = $result['previous_date'];

        if ($previousDate !== $validated['date_needed']) {
            $this->notificationService->notifyInspectionRequestRescheduled(
                $inspectionRequest,
                $previousDate,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Inspection preferred date updated successfully.',
            'inspection_request' => $inspectionRequest,
        ]);
    }

    public function assignedToTechnician(Request $request)
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Unauthorized. Only technicians can view assigned inspection requests.',
            ], 403);
        }

        $inspectionRequests = InspectionRequest::with('customer')
            ->where('technician_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'inspection_requests' => $inspectionRequests,
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
                'message' => 'Unauthorized. Only technicians can update inspection request status.',
            ], 403);
        }

        $inspectionRequest = InspectionRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);
        $previousStatus = $inspectionRequest->status;

        if ($inspectionRequest->technician_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You are not assigned to this inspection request.',
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
            'inspection_request' => $inspectionRequest,
        ]);
    }

    public function updateAdminStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,scheduled,assigned,in_progress,cancelled,declined,completed',
        ]);

        $inspectionRequest = InspectionRequest::query()
            ->with(['customer', 'technician'])
            ->findOrFail($id);
        $previousStatus = $inspectionRequest->status;

        $inspectionRequest->status = $request->status;
        $inspectionRequest->save();

        if ($previousStatus !== $inspectionRequest->status) {
            $this->notificationService->notifyCustomerOfInspectionRequestStatusUpdate(
                $inspectionRequest,
                $request->user()->id
            );
        }

        return response()->json([
            'message' => 'Official inspection request status updated successfully.',
            'inspection_request' => $inspectionRequest,
        ]);
    }
}
