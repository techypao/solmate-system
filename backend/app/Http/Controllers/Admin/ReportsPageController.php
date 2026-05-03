<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminReportDataService;
use Illuminate\Http\Request;

class ReportsPageController extends Controller
{
    public function show(Request $request, AdminReportDataService $reportDataService)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.reports', $reportDataService->buildReportsPageData(
            $request->query('range', 'this_month')
        ));
    }
}
