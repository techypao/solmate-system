<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportsPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $rangeOptions = [
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_year' => 'This Year',
            'all_time' => 'All Time',
        ];

        $selectedRange = $request->query('range', 'this_month');

        if (!array_key_exists($selectedRange, $rangeOptions)) {
            $selectedRange = 'this_month';
        }

        [$rangeStart, $rangeEnd] = $this->resolveRangeWindow($selectedRange);

        $customersCount = $this->applyCreatedAtRange(
            User::query()->where('role', User::ROLE_CUSTOMER),
            $rangeStart,
            $rangeEnd
        )->count();

        $techniciansCount = $this->applyCreatedAtRange(
            User::query()->where('role', User::ROLE_TECHNICIAN),
            $rangeStart,
            $rangeEnd
        )->count();

        $inspectionRequests = $this->applyCreatedAtRange(
            InspectionRequest::query()->with(['customer:id,name,email', 'technician:id,name,email']),
            $rangeStart,
            $rangeEnd
        )->latest()->get();

        $serviceRequests = $this->applyCreatedAtRange(
            ServiceRequest::query()->with(['customer:id,name,email', 'technician:id,name,email']),
            $rangeStart,
            $rangeEnd
        )->latest()->get();

        $quotations = $this->applyCreatedAtRange(
            Quotation::query()->with(['customer:id,name,email', 'inspectionRequest:id']),
            $rangeStart,
            $rangeEnd
        )->latest()->get();

        $installationRequests = $serviceRequests->filter(fn (ServiceRequest $request) => $this->isInstallationRequest($request));
        $maintenanceRequests = $serviceRequests->reject(fn (ServiceRequest $request) => $this->isInstallationRequest($request));
        $allRequests = $inspectionRequests->concat($serviceRequests)->values();

        $summaryCards = [
            ['label' => 'Total Customers', 'value' => $customersCount],
            ['label' => 'Total Technicians', 'value' => $techniciansCount],
            ['label' => 'Total Inspection Requests', 'value' => $inspectionRequests->count()],
            ['label' => 'Total Installation Requests', 'value' => $installationRequests->count()],
            ['label' => 'Total Maintenance Requests', 'value' => $maintenanceRequests->count()],
            ['label' => 'Total Quotations', 'value' => $quotations->count()],
            ['label' => 'Pending Requests', 'value' => $allRequests->filter(fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'pending')->count()],
            ['label' => 'In Progress Requests', 'value' => $allRequests->filter(fn ($requestItem) => $this->isRequestInProgress($requestItem->status))->count()],
            ['label' => 'Completed Requests', 'value' => $allRequests->filter(fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'completed')->count()],
        ];

        $chartPalette = ['#173b63', '#d4a017', '#3b82f6', '#22c55e', '#94a3b8'];

        $requestTypeChart = $this->buildChartDataset([
            'Inspection' => $inspectionRequests->count(),
            'Installation' => $installationRequests->count(),
            'Maintenance' => $maintenanceRequests->count(),
        ], $chartPalette);

        $requestStatusChart = $this->buildChartDataset([
            'Pending' => $allRequests->filter(fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'pending')->count(),
            'Approved / Scheduled' => $allRequests->filter(fn ($requestItem) => in_array($this->normalizeStatus($requestItem->status), ['approved', 'scheduled'], true))->count(),
            'In Progress' => $allRequests->filter(fn ($requestItem) => $this->isRequestInProgress($requestItem->status))->count(),
            'Completed' => $allRequests->filter(fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'completed')->count(),
            'Cancelled / Rejected' => $allRequests->filter(fn ($requestItem) => in_array($this->normalizeStatus($requestItem->status), ['cancelled', 'declined', 'rejected'], true))->count(),
        ], $chartPalette);

        $quotationTypeChart = $this->buildChartDataset([
            'Initial' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeQuotationType($quotation->quotation_type) === 'initial')->count(),
            'Final' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeQuotationType($quotation->quotation_type) === 'final')->count(),
        ], array_slice($chartPalette, 0, 2));

        $quotationStatusChart = $this->buildChartDataset([
            'Pending' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeStatus($quotation->status) === 'pending')->count(),
            'Approved' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeStatus($quotation->status) === 'approved')->count(),
            'Completed' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeStatus($quotation->status) === 'completed')->count(),
            'Rejected / Cancelled' => $quotations->filter(fn (Quotation $quotation) => in_array($this->normalizeStatus($quotation->status), ['rejected', 'cancelled', 'declined'], true))->count(),
        ], ['#173b63', '#d4a017', '#22c55e', '#94a3b8']);

        $technicianPerformance = User::query()
            ->where('role', User::ROLE_TECHNICIAN)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function (User $technician) use ($inspectionRequests, $serviceRequests) {
                $assignedInspectionRequests = $inspectionRequests->filter(fn (InspectionRequest $request) => (int) $request->technician_id === (int) $technician->id);
                $assignedServiceRequests = $serviceRequests->filter(fn (ServiceRequest $request) => (int) $request->technician_id === (int) $technician->id);
                $assignedRequests = $assignedInspectionRequests->concat($assignedServiceRequests);
                $totalAssigned = $assignedRequests->count();
                $completedRequests = $assignedRequests->filter(fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'completed')->count();
                $activeRequests = $assignedRequests->filter(fn ($requestItem) => $this->isActiveRequest($requestItem->status))->count();
                $completionRate = $totalAssigned > 0
                    ? round(($completedRequests / $totalAssigned) * 100, 1)
                    : 0;

                return [
                    'name' => $technician->name,
                    'email' => $technician->email,
                    'total_assigned' => $totalAssigned,
                    'completed_requests' => $completedRequests,
                    'active_requests' => $activeRequests,
                    'completion_rate' => $completionRate,
                ];
            })
            ->sortByDesc('total_assigned')
            ->values();

        $recentRequests = $allRequests
            ->map(function ($requestItem) {
                $isInspection = $requestItem instanceof InspectionRequest;
                $requestType = $isInspection
                    ? 'Inspection'
                    : ($this->isInstallationRequest($requestItem) ? 'Installation' : 'Maintenance');

                return [
                    'label' => $isInspection
                        ? "Inspection Request #{$requestItem->id}"
                        : "{$requestType} Request #{$requestItem->id}",
                    'type' => $requestType,
                    'customer_name' => $requestItem->customer?->name ?? 'Unknown customer',
                    'status' => Str::headline($this->normalizeStatus($requestItem->status)),
                    'created_at' => $requestItem->created_at,
                ];
            })
            ->sortByDesc(fn (array $item) => optional($item['created_at'])->timestamp ?? 0)
            ->take(5)
            ->values();

        $recentQuotations = $quotations
            ->map(function (Quotation $quotation) {
                return [
                    'label' => "Quotation #{$quotation->id}",
                    'type' => Str::headline($this->normalizeQuotationType($quotation->quotation_type)),
                    'customer_name' => $quotation->customer?->name ?? 'Unknown customer',
                    'status' => Str::headline($this->normalizeStatus($quotation->status)),
                    'created_at' => $quotation->created_at,
                ];
            })
            ->sortByDesc(fn (array $item) => optional($item['created_at'])->timestamp ?? 0)
            ->take(5)
            ->values();

        return view('admin.reports', [
            'rangeOptions' => $rangeOptions,
            'selectedRange' => $selectedRange,
            'selectedRangeLabel' => $rangeOptions[$selectedRange],
            'summaryCards' => $summaryCards,
            'requestTypeChart' => $requestTypeChart,
            'requestStatusChart' => $requestStatusChart,
            'quotationTypeChart' => $quotationTypeChart,
            'quotationStatusChart' => $quotationStatusChart,
            'technicianPerformance' => $technicianPerformance,
            'recentRequests' => $recentRequests,
            'recentQuotations' => $recentQuotations,
        ]);
    }

    private function resolveRangeWindow(string $rangeKey): array
    {
        $now = Carbon::now();

        return match ($rangeKey) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'all_time' => [null, null],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    private function applyCreatedAtRange(Builder $query, ?Carbon $start, ?Carbon $end): Builder
    {
        if (!$start || !$end) {
            return $query;
        }

        return $query->whereBetween('created_at', [$start, $end]);
    }

    private function isInstallationRequest(ServiceRequest $request): bool
    {
        return Str::of((string) ($request->request_type ?? ''))
            ->lower()
            ->replace(['_', '-'], ' ')
            ->contains('installation');
    }

    private function normalizeStatus(?string $status): string
    {
        return Str::of((string) $status)
            ->lower()
            ->replace('-', '_')
            ->value() ?: 'pending';
    }

    private function normalizeQuotationType(?string $type): string
    {
        return Str::of((string) $type)
            ->lower()
            ->trim()
            ->value() ?: 'initial';
    }

    private function isRequestInProgress(?string $status): bool
    {
        return in_array($this->normalizeStatus($status), ['assigned', 'scheduled', 'in_progress'], true);
    }

    private function isActiveRequest(?string $status): bool
    {
        return in_array($this->normalizeStatus($status), ['pending', 'approved', 'scheduled', 'assigned', 'in_progress'], true);
    }

    private function buildChartDataset(array $rows, array $colors): array
    {
        $labels = array_keys($rows);
        $values = array_values($rows);
        $total = array_sum($values);

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => array_slice($colors, 0, count($labels)),
            'total' => $total,
            'hasData' => $total > 0,
        ];
    }
}
