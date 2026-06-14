<?php

namespace App\Services;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminReportDataService
{
    public function rangeOptions(): array
    {
        return [
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_year' => 'This Year',
            'all_time' => 'All Time',
        ];
    }

    public function normalizeRangeKey(?string $rangeKey): string
    {
        $rangeOptions = $this->rangeOptions();

        return array_key_exists((string) $rangeKey, $rangeOptions)
            ? (string) $rangeKey
            : 'this_month';
    }

    public function buildReportsPageData(?string $rangeKey = null): array
    {
        $selectedRange = $this->normalizeRangeKey($rangeKey);
        $rangeOptions = $this->rangeOptions();
        [$rangeStart, $rangeEnd] = $this->resolveRangeWindow($selectedRange);
        $snapshot = $this->buildRangeSnapshot($rangeStart, $rangeEnd);

        $inspectionRequests = $snapshot['inspectionRequests'];
        $serviceRequests = $snapshot['serviceRequests'];
        $quotations = $snapshot['quotations'];
        $installationRequests = $snapshot['installationRequests'];
        $maintenanceRequests = $snapshot['maintenanceRequests'];
        $allRequests = $snapshot['allRequests'];
        $finalQuotations = $snapshot['finalQuotations'];
        $requestStatusCounts = $this->buildRequestStatusCounts($allRequests);
        $quotationStatusCounts = $this->buildQuotationStatusCounts($finalQuotations);

        $summaryCards = [
            ['label' => 'Total Customers', 'value' => $snapshot['customersCount']],
            ['label' => 'Total Technicians', 'value' => $snapshot['techniciansCount']],
            ['label' => 'Total Inspection Requests', 'value' => $inspectionRequests->count()],
            ['label' => 'Total Installation Requests', 'value' => $installationRequests->count()],
            ['label' => 'Total Maintenance Requests', 'value' => $maintenanceRequests->count()],
            ['label' => 'Total Quotations', 'value' => $quotations->count()],
            ['label' => 'Pending Requests', 'value' => $requestStatusCounts['pending']],
            ['label' => 'In Progress Requests', 'value' => $requestStatusCounts['in_progress']],
            ['label' => 'Completed Requests', 'value' => $requestStatusCounts['completed']],
        ];

        $chartPalette = ['#173b63', '#d4a017', '#3b82f6', '#22c55e', '#94a3b8'];

        return [
            'rangeOptions' => $rangeOptions,
            'selectedRange' => $selectedRange,
            'selectedRangeLabel' => $rangeOptions[$selectedRange],
            'summaryCards' => $summaryCards,
            'requestTypeChart' => $this->buildChartDataset([
                'Inspection' => $inspectionRequests->count(),
                'Installation' => $installationRequests->count(),
                'Maintenance' => $maintenanceRequests->count(),
            ], $chartPalette),
            'requestStatusChart' => $this->buildChartDataset([
                'Pending' => $requestStatusCounts['pending'],
                'Approved / Scheduled' => $requestStatusCounts['approved_scheduled'],
                'In Progress' => $requestStatusCounts['in_progress'],
                'Completed' => $requestStatusCounts['completed'],
                'Cancelled / Rejected' => $requestStatusCounts['cancelled_rejected'],
            ], $chartPalette),
            'quotationTypeChart' => $this->buildChartDataset([
                'Pre-Inspection' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeQuotationType($quotation->quotation_type) === 'initial')->count(),
                'Inspection-Based' => $quotations->filter(fn (Quotation $quotation) => $this->normalizeQuotationType($quotation->quotation_type) === 'final')->count(),
            ], array_slice($chartPalette, 0, 2)),
            'quotationStatusChart' => $this->buildChartDataset([
                'Pending' => $quotationStatusCounts['pending'],
                'Approved' => $quotationStatusCounts['approved'],
                'Completed' => $quotationStatusCounts['completed'],
                'Rejected / Cancelled' => $quotationStatusCounts['rejected_cancelled'],
            ], ['#173b63', '#d4a017', '#22c55e', '#94a3b8']),
            'technicianPerformance' => $this->buildTechnicianPerformance($inspectionRequests, $serviceRequests),
            'recentRequests' => $this->buildRecentRequests($allRequests),
            'recentQuotations' => $this->buildRecentQuotations($quotations),
        ];
    }

    public function buildMonthlySummary(?Carbon $rangeStart = null, ?Carbon $rangeEnd = null): array
    {
        $rangeStart = $rangeStart?->copy() ?? Carbon::now()->startOfMonth();
        $rangeEnd = $rangeEnd?->copy() ?? Carbon::now()->endOfMonth();
        $snapshot = $this->buildRangeSnapshot($rangeStart, $rangeEnd);
        $requestStatusCounts = $this->buildRequestStatusCounts($snapshot['allRequests']);
        $serviceRequestsCount = $snapshot['installationRequests']->count() + $snapshot['maintenanceRequests']->count();

        $metrics = [
            ['label' => 'Inspection Requests', 'value' => $snapshot['inspectionRequests']->count()],
            ['label' => 'Service Requests', 'value' => $serviceRequestsCount],
            ['label' => 'Quotations Generated', 'value' => $snapshot['quotations']->count()],
            ['label' => 'Approved / Scheduled', 'value' => $requestStatusCounts['approved_scheduled']],
            ['label' => 'Completed Requests', 'value' => $requestStatusCounts['completed']],
            ['label' => 'Pending Requests', 'value' => $requestStatusCounts['pending']],
        ];

        $activityTotal = array_sum(array_column($metrics, 'value'));

        return [
            'monthLabel' => $rangeStart->format('F Y'),
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'inspectionRequests' => $snapshot['inspectionRequests']->count(),
            'serviceRequests' => $serviceRequestsCount,
            'quotationsGenerated' => $snapshot['quotations']->count(),
            'approvedScheduledRequests' => $requestStatusCounts['approved_scheduled'],
            'completedRequests' => $requestStatusCounts['completed'],
            'pendingRequests' => $requestStatusCounts['pending'],
            'metrics' => $metrics,
            'hasData' => $activityTotal > 0,
        ];
    }

    public function resolveRangeWindow(string $rangeKey): array
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

    private function buildRangeSnapshot(?Carbon $rangeStart, ?Carbon $rangeEnd): array
    {
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

        $installationRequests = $serviceRequests->filter(
            fn (ServiceRequest $request) => $this->isInstallationRequest($request)
        )->values();

        $manualInspectionRequests = $serviceRequests->filter(
            fn (ServiceRequest $request) => $request->isManualInspectionRequest()
        )->values();

        $maintenanceRequests = $serviceRequests->reject(
            fn (ServiceRequest $request) => $this->isInstallationRequest($request) || $request->isManualInspectionRequest()
        )->values();

        $allInspectionRequests = $inspectionRequests->concat($manualInspectionRequests)->values();
        $allRequests = $inspectionRequests->concat($serviceRequests)->values();
        $finalQuotations = $quotations->filter(
            fn (Quotation $quotation) => $this->normalizeQuotationType($quotation->quotation_type) === 'final'
        )->values();

        return [
            'customersCount' => $this->applyCreatedAtRange(
                User::query()->where('role', User::ROLE_CUSTOMER),
                $rangeStart,
                $rangeEnd
            )->count(),
            'techniciansCount' => $this->applyCreatedAtRange(
                User::query()->where('role', User::ROLE_TECHNICIAN),
                $rangeStart,
                $rangeEnd
            )->count(),
            'inspectionRequests' => $allInspectionRequests,
            'serviceRequests' => $serviceRequests,
            'quotations' => $quotations,
            'installationRequests' => $installationRequests,
            'maintenanceRequests' => $maintenanceRequests,
            'allRequests' => $allRequests,
            'finalQuotations' => $finalQuotations,
        ];
    }

    private function buildRequestStatusCounts(Collection $allRequests): array
    {
        return [
            'pending' => $allRequests->filter(
                fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'pending'
            )->count(),
            'approved_scheduled' => $allRequests->filter(
                fn ($requestItem) => in_array($this->normalizeStatus($requestItem->status), ['approved', 'scheduled'], true)
            )->count(),
            'in_progress' => $allRequests->filter(
                fn ($requestItem) => $this->isRequestInProgress($requestItem->status)
            )->count(),
            'completed' => $allRequests->filter(
                fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'completed'
            )->count(),
            'cancelled_rejected' => $allRequests->filter(
                fn ($requestItem) => in_array($this->normalizeStatus($requestItem->status), ['cancelled', 'declined', 'rejected'], true)
            )->count(),
        ];
    }

    private function buildQuotationStatusCounts(Collection $finalQuotations): array
    {
        return [
            'pending' => $finalQuotations->filter(
                fn (Quotation $quotation) => $this->normalizeStatus($quotation->status) === 'pending'
            )->count(),
            'approved' => $finalQuotations->filter(
                fn (Quotation $quotation) => $this->normalizeStatus($quotation->status) === 'approved'
            )->count(),
            'completed' => $finalQuotations->filter(
                fn (Quotation $quotation) => $this->normalizeStatus($quotation->status) === 'completed'
            )->count(),
            'rejected_cancelled' => $finalQuotations->filter(
                fn (Quotation $quotation) => in_array($this->normalizeStatus($quotation->status), ['rejected', 'cancelled', 'declined'], true)
            )->count(),
        ];
    }

    private function buildTechnicianPerformance(Collection $inspectionRequests, Collection $serviceRequests): Collection
    {
        return User::query()
            ->where('role', User::ROLE_TECHNICIAN)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function (User $technician) use ($inspectionRequests, $serviceRequests) {
                $assignedInspectionRequests = $inspectionRequests->filter(
                    fn ($request) => (int) $request->technician_id === (int) $technician->id
                );
                $assignedServiceRequests = $serviceRequests->filter(
                    fn (ServiceRequest $request) => ! $request->isManualInspectionRequest()
                        && (int) $request->technician_id === (int) $technician->id
                );
                $assignedRequests = $assignedInspectionRequests->concat($assignedServiceRequests);
                $totalAssigned = $assignedRequests->count();
                $completedRequests = $assignedRequests->filter(
                    fn ($requestItem) => $this->normalizeStatus($requestItem->status) === 'completed'
                )->count();
                $activeRequests = $assignedRequests->filter(
                    fn ($requestItem) => $this->isActiveRequest($requestItem->status)
                )->count();

                return [
                    'name' => $technician->name,
                    'email' => $technician->email,
                    'total_assigned' => $totalAssigned,
                    'completed_requests' => $completedRequests,
                    'active_requests' => $activeRequests,
                    'completion_rate' => $totalAssigned > 0
                        ? round(($completedRequests / $totalAssigned) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('total_assigned')
            ->values();
    }

    private function buildRecentRequests(Collection $allRequests): Collection
    {
        return $allRequests
            ->map(function ($requestItem) {
                $isInspection = $requestItem instanceof InspectionRequest;
                $requestType = $isInspection || ($requestItem instanceof ServiceRequest && $requestItem->isManualInspectionRequest())
                    ? 'Inspection'
                    : ($this->isInstallationRequest($requestItem) ? 'Installation' : 'Maintenance');

                return [
                    'label' => $isInspection
                        ? "Inspection Request #{$requestItem->id}"
                        : "{$requestType} Request #{$requestItem->id}",
                    'type' => $requestType,
                    'customer_name' => $requestItem instanceof ServiceRequest
                        ? $requestItem->displayCustomerName()
                        : ($requestItem->customer?->name ?? 'Unknown customer'),
                    'status' => Str::headline($this->normalizeStatus($requestItem->status)),
                    'created_at' => $requestItem->created_at,
                ];
            })
            ->sortByDesc(fn (array $item) => optional($item['created_at'])->timestamp ?? 0)
            ->take(5)
            ->values();
    }

    private function buildRecentQuotations(Collection $quotations): Collection
    {
        return $quotations
            ->map(function (Quotation $quotation) {
                return [
                    'label' => "Quotation #{$quotation->id}",
                    'type' => $this->formatQuotationTypeLabel($quotation->quotation_type),
                    'customer_name' => $quotation->customer?->name ?? 'Unknown customer',
                    'status' => Str::headline($this->normalizeStatus($quotation->status)),
                    'created_at' => $quotation->created_at,
                ];
            })
            ->sortByDesc(fn (array $item) => optional($item['created_at'])->timestamp ?? 0)
            ->take(5)
            ->values();
    }

    private function applyCreatedAtRange(Builder $query, ?Carbon $rangeStart, ?Carbon $rangeEnd): Builder
    {
        if (! $rangeStart || ! $rangeEnd) {
            return $query;
        }

        return $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
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

    private function formatQuotationTypeLabel(?string $type): string
    {
        return match ($this->normalizeQuotationType($type)) {
            'final' => 'Inspection-Based',
            default => 'Pre-Inspection',
        };
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
