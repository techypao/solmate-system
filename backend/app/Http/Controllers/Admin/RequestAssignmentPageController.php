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
                ->latest()
                ->get(),
            'inspectionRequests' => InspectionRequest::query()
                ->with(['customer', 'technician'])
                ->latest()
                ->get(),
        ]);
    }
}
