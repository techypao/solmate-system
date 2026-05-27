<?php

namespace App\Http\Controllers;

use App\Models\CompletionReport;
use App\Models\CompletionReportPhoto;
use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompletionReportController extends Controller
{
    public function __construct(private InAppNotificationService $notificationService)
    {
    }

    public function submitForService(Request $request, int $id)
    {
        $validated = $request->validate($this->serviceRules());
        $technician = $request->user();

        if ($technician->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Only technicians can submit service completion notes.',
            ], 403);
        }

        $serviceRequest = ServiceRequest::query()
            ->with(['customer', 'technician', 'completionReport'])
            ->findOrFail($id);

        if ((int) $serviceRequest->technician_id !== (int) $technician->id) {
            return response()->json([
                'message' => 'You are not allowed to submit completion notes for this service request.',
            ], 403);
        }

        if ($serviceRequest->status !== 'in_progress') {
            return response()->json([
                'message' => 'Service completion notes can only be submitted while the task is in progress.',
            ], 422);
        }

        if ($serviceRequest->completionReport) {
            return response()->json([
                'message' => $serviceRequest->completionReport->status === CompletionReport::STATUS_APPROVED
                    ? 'Completion notes for this service request have already been approved.'
                    : 'Completion notes for this service request are already awaiting admin approval.',
            ], 422);
        }

        DB::transaction(function () use ($serviceRequest, $technician, $validated) {
            $report = CompletionReport::query()->create([
                'service_request_id' => $serviceRequest->id,
                'technician_id' => $technician->id,
                'report_text' => trim($validated['report_text']),
                'status' => CompletionReport::STATUS_PENDING,
                'completed_at' => $validated['completed_at'],
                'submitted_at' => now(),
            ]);

            $serviceRequest->technician_marked_done_at = now();
            $serviceRequest->save();

            $storedPaths = [];
            try {
                foreach ($validated['completion_photos'] as $photo) {
                    $path = $photo->store("completion-reports/{$report->id}", CompletionReportPhoto::PUBLIC_DISK);
                    $storedPaths[] = $path;
                    CompletionReportPhoto::query()->create([
                        'completion_report_id' => $report->id,
                        'image_path' => $path,
                    ]);
                }
            } catch (\Throwable $throwable) {
                foreach ($storedPaths as $path) {
                    Storage::disk(CompletionReportPhoto::PUBLIC_DISK)->delete($path);
                }
                throw $throwable;
            }
        });

        $serviceRequest->load(['customer', 'technician', 'completionReport.technician', 'completionReport.approver', 'completionReport.photos']);
        $this->notificationService->notifyAdminsOfCompletionReportSubmission($serviceRequest, $technician->id);

        return response()->json([
            'message' => 'Service completion notes submitted for admin approval.',
            'data' => $serviceRequest,
        ], 201);
    }

    public function submitForInspection(Request $request, int $id)
    {
        $validated = $request->validate($this->inspectionRules());
        $technician = $request->user();

        if ($technician->role !== User::ROLE_TECHNICIAN) {
            return response()->json([
                'message' => 'Only technicians can submit inspection completion notes.',
            ], 403);
        }

        $inspectionRequest = InspectionRequest::query()
            ->with(['customer', 'technician', 'completionReport'])
            ->findOrFail($id);

        if ((int) $inspectionRequest->technician_id !== (int) $technician->id) {
            return response()->json([
                'message' => 'You are not allowed to submit completion notes for this inspection request.',
            ], 403);
        }

        if ($inspectionRequest->status !== 'in_progress') {
            return response()->json([
                'message' => 'Inspection completion notes can only be submitted while the task is in progress.',
            ], 422);
        }

        if ($inspectionRequest->completionReport) {
            return response()->json([
                'message' => $inspectionRequest->completionReport->status === CompletionReport::STATUS_APPROVED
                    ? 'Completion notes for this inspection request have already been approved.'
                    : 'Completion notes for this inspection request are already awaiting admin approval.',
            ], 422);
        }

        if (! $inspectionRequest->finalQuotation()->exists()) {
            return response()->json([
                'message' => 'Create the inspection-based quotation before notifying admin that this inspection is done.',
            ], 422);
        }

        DB::transaction(function () use ($inspectionRequest, $technician, $validated) {
            $report = CompletionReport::query()->create([
                'inspection_request_id' => $inspectionRequest->id,
                'technician_id' => $technician->id,
                'report_text' => trim($validated['report_text']),
                'status' => CompletionReport::STATUS_PENDING,
                'completed_at' => $validated['completed_at'],
                'submitted_at' => now(),
            ]);

            $storedPaths = [];
            try {
                foreach ($validated['completion_photos'] as $photo) {
                    $path = $photo->store("completion-reports/{$report->id}", CompletionReportPhoto::PUBLIC_DISK);
                    $storedPaths[] = $path;
                    CompletionReportPhoto::query()->create([
                        'completion_report_id' => $report->id,
                        'image_path' => $path,
                    ]);
                }
            } catch (\Throwable $throwable) {
                foreach ($storedPaths as $path) {
                    Storage::disk(CompletionReportPhoto::PUBLIC_DISK)->delete($path);
                }

                throw $throwable;
            }
        });

        $inspectionRequest->load(['customer', 'technician', 'completionReport.technician', 'completionReport.approver', 'completionReport.photos']);
        $inspectionRequest->setAttribute('has_final_quotation', true);
        $this->notificationService->notifyAdminsOfCompletionReportSubmission($inspectionRequest, $technician->id);

        return response()->json([
            'message' => 'Inspection completion notes submitted for admin approval.',
            'inspection_request' => $inspectionRequest,
        ], 201);
    }

    private function serviceRules(): array
    {
        return [
            'report_text' => ['required', 'string'],
            'completed_at' => ['required', 'date'],
            'completion_photos' => ['required', 'array', 'min:1'],
            'completion_photos.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private function inspectionRules(): array
    {
        return [
            'report_text' => ['required', 'string'],
            'completed_at' => ['required', 'date'],
            'completion_photos' => ['required', 'array', 'min:1'],
            'completion_photos.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
