<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RequestAssignmentPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.request-assignments', [
            'technicians' => User::query()
                ->where('role', User::ROLE_TECHNICIAN)
                ->orderBy('name')
                ->get(),
            'serviceRequests' => ServiceRequest::query()
                ->with(['customer', 'technician'])
                ->orderByRaw("CASE WHEN technician_marked_done_at IS NOT NULL AND status != 'completed' THEN 0 WHEN technician_id IS NULL THEN 1 ELSE 2 END")
                ->latest()
                ->get(),
            'inspectionRequests' => InspectionRequest::query()
                ->with(['customer', 'technician'])
                ->orderByRaw('CASE WHEN technician_id IS NULL THEN 0 ELSE 1 END')
                ->latest()
                ->get(),
        ]);
    }
}
