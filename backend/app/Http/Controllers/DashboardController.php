<?php

namespace App\Http\Controllers;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AdminReportDataService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request, AdminReportDataService $reportDataService)
    {
        $user = $request->user();

        $viewData = [
            'user' => $user,
        ];

        if ($user?->role === User::ROLE_ADMIN) {
            $viewData = array_merge($viewData, $this->buildAdminDashboardData($reportDataService));
        }

        return view('dashboard', $viewData);
    }

    private function buildAdminDashboardData(AdminReportDataService $reportDataService): array
    {
        return [
            'adm_totalCustomers' => User::query()
                ->where('role', User::ROLE_CUSTOMER)
                ->count(),
            'adm_totalTechnicians' => User::query()
                ->where('role', User::ROLE_TECHNICIAN)
                ->count(),
            'adm_pendingInspCount' => InspectionRequest::query()
                ->where('status', 'pending')
                ->count(),
            'adm_pendingServiceCount' => ServiceRequest::query()
                ->where('status', 'pending')
                ->count(),
            'adm_initialQuotations' => Quotation::query()
                ->where('quotation_type', 'initial')
                ->count(),
            'adm_finalQuotations' => Quotation::query()
                ->where('quotation_type', 'final')
                ->count(),
            'adm_pendingInspections' => InspectionRequest::query()
                ->with(['customer', 'technician'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
            'adm_pendingServices' => ServiceRequest::query()
                ->with(['customer', 'technician'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
            'adm_recentQuotations' => Quotation::query()
                ->with('customer')
                ->latest()
                ->limit(5)
                ->get(),
            'monthlyReport' => $reportDataService->buildMonthlySummary(),
        ];
    }
}
