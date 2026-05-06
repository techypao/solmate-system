<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RequestAssignmentPageController extends Controller
{
    private const SERVICE_POPUP_MESSAGES = [
        'technician_assigned' => 'Technician has been successfully assigned.',
        'preferred_date_changed' => 'Preferred date has been successfully updated.',
        'status_changed' => 'Service status has been successfully updated.',
        'inspection_technician_assigned' => 'Technician has been successfully assigned.',
        'inspection_preferred_date_changed' => 'Preferred date has been successfully updated.',
        'inspection_status_changed' => 'Inspection status has been successfully updated.',
    ];

    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.request-assignments', [
            'technicians' => User::query()
                ->where('role', User::ROLE_TECHNICIAN)
                ->orderBy('name')
                ->get(),
            'serviceRequests' => ServiceRequest::query()
                ->with(['customer', 'technician', 'completionReport.technician', 'completionReport.approver'])
                ->orderByRaw("CASE WHEN technician_marked_done_at IS NOT NULL AND status != 'completed' THEN 0 WHEN technician_id IS NULL THEN 1 ELSE 2 END")
                ->latest()
                ->get(),
            'inspectionRequests' => InspectionRequest::query()
                ->with(['customer', 'technician', 'completionReport.technician', 'completionReport.approver'])
                ->orderByRaw('CASE WHEN technician_id IS NULL THEN 0 ELSE 1 END')
                ->latest()
                ->get(),
        ]);
    }

    public function flashServicePopup(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(array_keys(self::SERVICE_POPUP_MESSAGES))],
            'redirect_to' => ['nullable', 'string', 'max:255'],
        ]);

        $redirectFragment = $this->sanitizeRedirectFragment($validated['redirect_to'] ?? null);

        $request->session()->flash('admin_service_popup', [
            'action' => $validated['action'],
            'message' => self::SERVICE_POPUP_MESSAGES[$validated['action']],
        ]);

        return redirect()->to(route('admin.request-assignments') . $redirectFragment);
    }

    private function sanitizeRedirectFragment(?string $fragment): string
    {
        if (! is_string($fragment) || $fragment === '' || ! str_starts_with($fragment, '#')) {
            return '';
        }

        return preg_match('/^#(?:service-request-\d+|inspection-request-\d+|inspection-requests-section|installation-requests-section|maintenance-requests-section|service-requests-section|services-section)$/', $fragment)
            ? $fragment
            : '';
    }
}
