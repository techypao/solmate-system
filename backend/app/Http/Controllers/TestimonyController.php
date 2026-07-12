<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminModerateTestimonyRequest;
use App\Http\Requests\StoreTestimonyRequest;
use App\Http\Requests\UpdateTestimonyRequest;
use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\Testimony;
use App\Services\CustomerActivityLogger;
use App\Services\InAppNotificationService;
use App\Services\TestimonyContentModerationService;
use App\Services\TestimonyImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonyController extends Controller
{
    public function __construct(
        private TestimonyImageService $testimonyImageService,
        private TestimonyContentModerationService $contentModerationService,
        private InAppNotificationService $notificationService
    ) {}

    public function publicIndex(): JsonResponse
    {
        $testimonies = $this->testimonyQuery()
            ->where('status', Testimony::STATUS_APPROVED)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Approved testimonies retrieved successfully.',
            'data' => $testimonies,
        ], 200);
    }

    public function myIndex(Request $request): JsonResponse
    {
        $testimonies = $this->testimonyQuery()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Your testimonies retrieved successfully.',
            'data' => $testimonies,
        ], 200);
    }

    public function store(StoreTestimonyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $eligibilityError = $this->validateLinkedRequests(
            $request->user()->id,
            $validated['service_request_id'] ?? null,
            $validated['inspection_request_id'] ?? null,
        );

        if ($eligibilityError) {
            return $eligibilityError;
        }

        $moderationNote = $this->contentModerationService->rejectionNote(
            $validated['title'] ?? null,
            $validated['message']
        );

        $testimony = DB::transaction(function () use ($request, $validated, $moderationNote) {
            $testimony = Testimony::query()->create([
                'user_id' => $request->user()->id,
                'service_request_id' => $validated['service_request_id'] ?? null,
                'inspection_request_id' => $validated['inspection_request_id'] ?? null,
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'message' => $validated['message'],
                'status' => $moderationNote ? Testimony::STATUS_REJECTED : Testimony::STATUS_PENDING,
                'admin_note' => $moderationNote,
            ]);

            $this->testimonyImageService->syncForStore(
                $testimony,
                $request->file('images', [])
            );

            return $testimony;
        });

        $testimony->load($this->relationships());

        if (! $moderationNote) {
            $this->notificationService->notifyAdminsOfNewTestimony($testimony, $request->user()->id);
        }

        app(CustomerActivityLogger::class)->record(
            customer: $request->user(),
            eventType: 'testimony_created',
            title: 'Feedback submitted',
            description: 'Customer submitted feedback with a '.$testimony->rating.'/5 rating.',
            actor: $request->user(),
            subject: $testimony,
            metadata: [
                'status' => $testimony->status,
                'rating' => $testimony->rating,
            ],
        );

        return response()->json([
            'message' => $moderationNote
                ? 'Testimony submitted and automatically rejected by content moderation.'
                : 'Testimony submitted successfully.',
            'data' => $testimony,
        ], 201);
    }

    public function update(UpdateTestimonyRequest $request, int $id): JsonResponse
    {
        $testimony = $this->findCustomerTestimony($request->user()->id, $id);

        if (! $testimony) {
            return response()->json([
                'message' => 'Testimony not found.',
            ], 404);
        }

        $validated = $request->validated();
        $serviceRequestId = array_key_exists('service_request_id', $validated)
            ? $validated['service_request_id']
            : $testimony->service_request_id;
        $inspectionRequestId = array_key_exists('inspection_request_id', $validated)
            ? $validated['inspection_request_id']
            : $testimony->inspection_request_id;

        $eligibilityError = $this->validateLinkedRequests(
            $request->user()->id,
            $serviceRequestId,
            $inspectionRequestId,
        );

        if ($eligibilityError) {
            return $eligibilityError;
        }

        $previousStatus = $testimony->status;
        $moderationNote = $this->contentModerationService->rejectionNote(
            $validated['title'] ?? $testimony->title,
            $validated['message']
        );

        DB::transaction(function () use ($request, $validated, $testimony, $serviceRequestId, $inspectionRequestId, $previousStatus, $moderationNote): void {
            $testimony->rating = $validated['rating'];
            $testimony->message = $validated['message'];
            $testimony->service_request_id = $serviceRequestId;
            $testimony->inspection_request_id = $inspectionRequestId;

            if (array_key_exists('title', $validated)) {
                $testimony->title = $validated['title'];
            }

            if ($moderationNote) {
                $testimony->status = Testimony::STATUS_REJECTED;
                $testimony->admin_note = $moderationNote;
            } elseif ($previousStatus !== Testimony::STATUS_PENDING) {
                $testimony->status = Testimony::STATUS_PENDING;
                $testimony->admin_note = null;
            }

            $testimony->save();

            $this->testimonyImageService->syncForUpdate(
                $testimony,
                $request->file('images', []),
                $validated['remove_image_ids'] ?? []
            );
        });

        $testimony->load($this->relationships());

        app(CustomerActivityLogger::class)->record(
            customer: $request->user(),
            eventType: 'testimony_updated',
            title: 'Feedback updated',
            description: 'Customer updated feedback.',
            actor: $request->user(),
            subject: $testimony,
            metadata: [
                'status' => $testimony->status,
                'rating' => $testimony->rating,
            ],
        );

        return response()->json([
            'message' => $moderationNote
                ? 'Testimony updated and automatically rejected by content moderation.'
                : ($previousStatus !== Testimony::STATUS_PENDING
                ? 'Testimony updated successfully and sent back for review.'
                : 'Testimony updated successfully.'),
            'data' => $testimony,
        ], 200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $testimony = $this->findCustomerTestimony($request->user()->id, $id);

        if (! $testimony) {
            return response()->json([
                'message' => 'Testimony not found.',
            ], 404);
        }

        app(CustomerActivityLogger::class)->record(
            customer: $request->user(),
            eventType: 'testimony_deleted',
            title: 'Feedback deleted',
            description: 'Customer deleted feedback.',
            actor: $request->user(),
            subject: $testimony,
            metadata: [
                'testimony_id' => $testimony->id,
            ],
        );

        $testimony->delete();

        return response()->json([
            'message' => 'Testimony deleted successfully.',
        ], 200);
    }

    public function adminIndex(): JsonResponse
    {
        $testimonies = $this->testimonyQuery()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'All testimonies retrieved successfully.',
            'data' => $testimonies,
        ], 200);
    }

    public function approve(AdminModerateTestimonyRequest $request, int $id): JsonResponse
    {
        $testimony = $this->findTestimony($id);

        if (! $testimony) {
            return response()->json([
                'message' => 'Testimony not found.',
            ], 404);
        }

        $validated = $request->validated();
        $testimony->status = Testimony::STATUS_APPROVED;
        $testimony->admin_note = $validated['admin_note'] ?? null;
        $testimony->save();
        $testimony->load($this->relationships());

        return response()->json([
            'message' => 'Testimony approved successfully.',
            'data' => $testimony,
        ], 200);
    }

    public function reject(AdminModerateTestimonyRequest $request, int $id): JsonResponse
    {
        $testimony = $this->findTestimony($id);

        if (! $testimony) {
            return response()->json([
                'message' => 'Testimony not found.',
            ], 404);
        }

        $validated = $request->validated();
        $testimony->status = Testimony::STATUS_REJECTED;
        $testimony->admin_note = $validated['admin_note'] ?? null;
        $testimony->save();
        $testimony->load($this->relationships());

        return response()->json([
            'message' => 'Testimony rejected successfully.',
            'data' => $testimony,
        ], 200);
    }

    public function adminUpdate(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Admins are not allowed to edit testimonies.',
        ], 403);
    }

    public function adminDestroy(int $id): JsonResponse
    {
        $testimony = $this->findTestimony($id);

        if (! $testimony) {
            return response()->json([
                'message' => 'Testimony not found.',
            ], 404);
        }

        $testimony->delete();

        return response()->json([
            'message' => 'Testimony deleted successfully.',
        ], 200);
    }

    private function testimonyQuery()
    {
        return Testimony::query()->with($this->relationships());
    }

    private function relationships(): array
    {
        return [
            'user:id,name,profile_picture',
            'serviceRequest:id,user_id,technician_id,request_type,status,date_needed',
            'inspectionRequest:id,user_id,technician_id,status,date_needed',
            'images',
        ];
    }

    private function validateLinkedRequests(
        int $userId,
        ?int $serviceRequestId,
        ?int $inspectionRequestId
    ): ?JsonResponse {
        if (! $serviceRequestId && ! $inspectionRequestId) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'service_request_id' => [
                        'Either service_request_id or inspection_request_id is required.',
                    ],
                ],
            ], 422);
        }

        if ($serviceRequestId) {
            $serviceRequest = ServiceRequest::query()->find($serviceRequestId);

            if (! $serviceRequest) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'service_request_id' => ['The selected service request is invalid.'],
                    ],
                ], 422);
            }

            if ((int) $serviceRequest->user_id !== $userId) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'service_request_id' => ['You may only link your own completed service requests.'],
                    ],
                ], 422);
            }

            if ($serviceRequest->status !== 'completed') {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'service_request_id' => ['Only completed service requests can be linked to a testimony.'],
                    ],
                ], 422);
            }
        }

        if ($inspectionRequestId) {
            $inspectionRequest = InspectionRequest::query()->find($inspectionRequestId);

            if (! $inspectionRequest) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'inspection_request_id' => ['The selected inspection request is invalid.'],
                    ],
                ], 422);
            }

            if ((int) $inspectionRequest->user_id !== $userId) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'inspection_request_id' => ['You may only link your own completed inspection requests.'],
                    ],
                ], 422);
            }

            if ($inspectionRequest->status !== 'completed') {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'inspection_request_id' => ['Only completed inspection requests can be linked to a testimony.'],
                    ],
                ], 422);
            }
        }

        return null;
    }

    private function findCustomerTestimony(int $userId, int $id): ?Testimony
    {
        return Testimony::query()
            ->where('user_id', $userId)
            ->find($id);
    }

    private function findTestimony(int $id): ?Testimony
    {
        return Testimony::query()->find($id);
    }
}
