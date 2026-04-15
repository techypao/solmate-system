<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Models\ServiceRequest;
use App\Models\InspectionRequest;
use App\Services\QuotationComputationService;
use App\Services\QuotationSettingsService;

class QuotationController extends Controller
{
    private QuotationComputationService $quotationComputationService;
    private QuotationSettingsService $quotationSettingsService;

    public function __construct(
        QuotationComputationService $quotationComputationService,
        QuotationSettingsService $quotationSettingsService
    )
    {
        $this->quotationComputationService = $quotationComputationService;
        $this->quotationSettingsService = $quotationSettingsService;
    }

    public function index()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'technician'])) {
            $quotations = Quotation::with('user')->latest()->get();
        } else {
            $quotations = Quotation::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json($quotations);
    }

    public function store(Request $request)
    {
        try {
            // Customer submissions should stay simple: only accept the bill and remarks.
            // The backend enforces the default initial hybrid quotation setup.
            $validated = $request->validate(
                $this->storeRules(),
                $this->storeMessages()
            );

            $monthlyElectricBill = $validated['monthly_electric_bill'];
            $defaultSettings = $this->quotationSettingsService->current();
            $ratePerKwh = (float) $defaultSettings['rate_per_kwh'];
            $daysInMonth = (int) $defaultSettings['days_in_month'];
            $sunHours = (float) $defaultSettings['sun_hours'];
            $pvSafetyFactor = (float) $defaultSettings['pv_safety_factor'];
            $batteryFactor = (float) $defaultSettings['battery_factor'];
            $batteryVoltage = (float) $defaultSettings['battery_voltage'];
            $panelWatts = (float) $defaultSettings['default_panel_watts'];
            $withBattery = true;
            $defaultSystemType = (string) config('quotation_options.initial_quotation.default_system_type', 'hybrid');
            $pricePerKw = (float) ($defaultSettings['initial_price_per_kw']
                ?? config('quotation_settings.defaults.initial_price_per_kw', 50000));

            $computedValues = $this->quotationComputationService->computeSizing([
                'monthly_electric_bill' => $monthlyElectricBill,
                'rate_per_kwh' => $ratePerKwh,
                'days_in_month' => $daysInMonth,
                'sun_hours' => $sunHours,
                'pv_safety_factor' => $pvSafetyFactor,
                'battery_factor' => $batteryFactor,
                'battery_voltage' => $batteryVoltage,
                'panel_watts' => $panelWatts,
                'with_battery' => $withBattery,
            ]);
            $estimatedProjectCost = $this->quotationComputationService->estimatePackageProjectCost(
                $computedValues['system_kw'],
                $pricePerKw
            );
            $roiValues = $this->quotationComputationService->computeRoi(
                $estimatedProjectCost,
                $monthlyElectricBill
            );

            $quotation = Quotation::create([
                'user_id' => Auth::id(),
                'quotation_type' => 'initial',
                'monthly_electric_bill' => $monthlyElectricBill,
                'rate_per_kwh' => $ratePerKwh,
                'days_in_month' => $daysInMonth,
                'sun_hours' => $sunHours,
                'pv_safety_factor' => $pvSafetyFactor,
                'battery_factor' => $batteryFactor,
                'battery_voltage' => $batteryVoltage,
                'pv_system_type' => $defaultSystemType,
                'with_battery' => $withBattery,
                'inverter_type' => null,
                'battery_model' => null,
                'battery_capacity_ah' => null,
                'panel_watts' => $panelWatts,
                'monthly_kwh' => $computedValues['monthly_kwh'],
                'daily_kwh' => $computedValues['daily_kwh'],
                'pv_kw_raw' => $computedValues['pv_kw_raw'],
                'pv_kw_safe' => $computedValues['pv_kw_safe'],
                'panel_quantity' => $computedValues['panel_quantity'],
                'system_kw' => $computedValues['system_kw'],
                'battery_required_kwh' => $computedValues['battery_required_kwh'],
                'battery_required_ah' => $computedValues['battery_required_ah'],
                'panel_cost' => null,
                'inverter_cost' => null,
                'battery_cost' => null,
                'bos_cost' => null,
                'materials_subtotal' => null,
                'labor_cost' => null,
                'project_cost' => $estimatedProjectCost,
                'estimated_monthly_savings' => $roiValues['estimated_monthly_savings'],
                'estimated_annual_savings' => $roiValues['estimated_annual_savings'],
                'roi_years' => $roiValues['roi_years'],
                'status' => 'pending',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            return response()->json([
                'message' => 'Quotation created successfully',
                'data' => $quotation,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
        
    }

    public function show($id)
    {
        $user = Auth::user();

        $quotation = Quotation::with('user')->find($id);

        if (!$quotation) {
            return response()->json([
                'message' => 'Quotation not found'
            ], 404);
        }

        if (!in_array($user->role, ['admin', 'technician']) && $quotation->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $this->loadLineItemsForFinalQuotation($quotation);

        return response()->json($quotation);
    }

    public function getFinalQuotationOptions()
    {
        return response()->json([
            'message' => 'Final quotation options retrieved successfully.',
            'data' => $this->finalQuotationOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'technician'])) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $quotation = Quotation::find($id);

        if (!$quotation) {
            return response()->json([
                'message' => 'Quotation not found'
            ], 404);
        }

        $validated = $request->validate(
            $this->updateRules(),
            $this->updateMessages()
        );

        // Technician updates always finalize the quotation on the backend,
        // even if the client omits or changes quotation_type.
        if ($user->role === 'technician') {
            $validated['quotation_type'] = 'final';
        }

        $monthlyElectricBill = $quotation->monthly_electric_bill ?? 0;
        $ratePerKwh = $quotation->rate_per_kwh ?? 14;
        $daysInMonth = $quotation->days_in_month ?? 30;
        $sunHours = $quotation->sun_hours ?? 4.5;
        $pvSafetyFactor = $quotation->pv_safety_factor ?? 1.8;
        $batteryFactor = $quotation->battery_factor ?? 1.0;
        $batteryVoltage = $quotation->battery_voltage ?? 51.2;
        $panelWatts = $validated['panel_watts'] ?? $quotation->panel_watts ?? 610;
        $withBattery = $quotation->with_battery ?? true;

        $computedValues = $this->quotationComputationService->computeSizing([
            'monthly_electric_bill' => $monthlyElectricBill,
            'rate_per_kwh' => $ratePerKwh,
            'days_in_month' => $daysInMonth,
            'sun_hours' => $sunHours,
            'pv_safety_factor' => $pvSafetyFactor,
            'battery_factor' => $batteryFactor,
            'battery_voltage' => $batteryVoltage,
            'panel_watts' => $panelWatts,
            'with_battery' => $withBattery,
        ]);

        $projectCost = $validated['project_cost'] ?? $quotation->project_cost;
        $roiValues = $this->quotationComputationService->computeRoi(
            $projectCost,
            $monthlyElectricBill
        );

        $quotation->update(array_merge($validated, [
            'monthly_kwh' => $computedValues['monthly_kwh'],
            'daily_kwh' => $computedValues['daily_kwh'],
            'pv_kw_raw' => $computedValues['pv_kw_raw'],
            'pv_kw_safe' => $computedValues['pv_kw_safe'],
            'panel_quantity' => $computedValues['panel_quantity'],
            'system_kw' => $computedValues['system_kw'],
            'battery_required_kwh' => $computedValues['battery_required_kwh'],
            'battery_required_ah' => $computedValues['battery_required_ah'],
        ], $roiValues));

        return response()->json([
            'message' => 'Quotation updated successfully',
            'data' => $quotation,
        ]);
    }
public function storeFinalQuotation(Request $request)
{
    $validated = $request->validate(
        $this->storeFinalQuotationRules(),
        $this->storeFinalQuotationMessages()
    );

    $technician = $request->user();

    if ($technician->role !== 'technician') {
        return response()->json([
            'message' => 'Only technicians can create final quotations.'
        ], 403);
    }

    $inspectionRequest = InspectionRequest::findOrFail($validated['inspection_request_id']);

    if ($inspectionRequest->technician_id !== $technician->id) {
        return response()->json([
            'message' => 'You are not assigned to this inspection request.'
        ], 403);
    }

    if ($inspectionRequest->status !== 'completed') {
        return response()->json([
            'message' => 'Final quotation can only be created when the inspection request is completed.'
        ], 422);
    }

    $existingFinalQuotation = Quotation::where('inspection_request_id', $inspectionRequest->id)
        ->where('quotation_type', 'final')
        ->first();

    if ($existingFinalQuotation) {
        return response()->json([
            'message' => 'A final quotation already exists for this inspection request.'
        ], 422);
    }

    $monthlyElectricBill = $validated['monthly_electric_bill'];
    $defaultSettings = $this->quotationSettingsService->current();
    $ratePerKwh = (float) ($validated['rate_per_kwh'] ?? $defaultSettings['rate_per_kwh']);
    $daysInMonth = (int) ($validated['days_in_month'] ?? $defaultSettings['days_in_month']);
    $sunHours = (float) ($validated['sun_hours'] ?? $defaultSettings['sun_hours']);
    $pvSafetyFactor = (float) ($validated['pv_safety_factor'] ?? $defaultSettings['pv_safety_factor']);
    $batteryFactor = (float) ($validated['battery_factor'] ?? $defaultSettings['battery_factor']);
    $batteryVoltage = (float) ($validated['battery_voltage'] ?? $defaultSettings['battery_voltage']);
    $panelWatts = (float) ($validated['panel_watts'] ?? $defaultSettings['default_panel_watts']);
    $withBattery = $validated['with_battery'];

    $computedValues = $this->quotationComputationService->computeSizing([
        'monthly_electric_bill' => $monthlyElectricBill,
        'rate_per_kwh' => $ratePerKwh,
        'days_in_month' => $daysInMonth,
        'sun_hours' => $sunHours,
        'pv_safety_factor' => $pvSafetyFactor,
        'battery_factor' => $batteryFactor,
        'battery_voltage' => $batteryVoltage,
        'panel_watts' => $panelWatts,
        'with_battery' => $withBattery,
    ]);

    $projectCost = $validated['project_cost'] ?? null;
    $roiValues = $this->quotationComputationService->computeRoi(
        $projectCost,
        $monthlyElectricBill
    );

    $quotation = Quotation::create([
        'user_id' => $inspectionRequest->user_id,
        'inspection_request_id' => $inspectionRequest->id,
        'quotation_type' => 'final',
        'monthly_electric_bill' => $monthlyElectricBill,
        'rate_per_kwh' => $ratePerKwh,
        'days_in_month' => $daysInMonth,
        'sun_hours' => $sunHours,
        'pv_safety_factor' => $pvSafetyFactor,
        'battery_factor' => $batteryFactor,
        'battery_voltage' => $batteryVoltage,
        'pv_system_type' => $validated['pv_system_type'],
        'with_battery' => $withBattery,
        'inverter_type' => $validated['inverter_type'] ?? null,
        'battery_model' => $validated['battery_model'] ?? null,
        'battery_capacity_ah' => $validated['battery_capacity_ah'] ?? null,
        'panel_watts' => $panelWatts,
        'monthly_kwh' => $computedValues['monthly_kwh'],
        'daily_kwh' => $computedValues['daily_kwh'],
        'pv_kw_raw' => $computedValues['pv_kw_raw'],
        'pv_kw_safe' => $computedValues['pv_kw_safe'],
        'panel_quantity' => $computedValues['panel_quantity'],
        'system_kw' => $computedValues['system_kw'],
        'battery_required_kwh' => $computedValues['battery_required_kwh'],
        'battery_required_ah' => $computedValues['battery_required_ah'],
        'panel_cost' => $validated['panel_cost'] ?? null,
        'inverter_cost' => $validated['inverter_cost'] ?? null,
        'battery_cost' => $validated['battery_cost'] ?? null,
        'bos_cost' => $validated['bos_cost'] ?? null,
        'materials_subtotal' => $validated['materials_subtotal'] ?? null,
        'labor_cost' => $validated['labor_cost'] ?? null,
        'project_cost' => $validated['project_cost'] ?? null,
        'estimated_monthly_savings' => $roiValues['estimated_monthly_savings'],
        'estimated_annual_savings' => $roiValues['estimated_annual_savings'],
        'roi_years' => $roiValues['roi_years'],
        'status' => $validated['status'] ?? 'pending',
        'remarks' => $validated['remarks'] ?? null,
    ]);

    $quotation->load(['customer', 'inspectionRequest']);

    return response()->json([
        'message' => 'Final quotation created successfully.',
        'data' => $quotation
    ], 201);
}

public function getCustomerFinalQuotation(Request $request, $inspection_request_id)
{
    $customer = $request->user();

    $quotation = Quotation::with(['customer', 'inspectionRequest'])
        ->where('inspection_request_id', $inspection_request_id)
        ->where('user_id', $customer->id)
        ->where('quotation_type', 'final')
        ->first();

    if (!$quotation) {
        return response()->json([
            'message' => 'Final quotation not found.'
        ], 404);
    }

    $this->loadLineItemsForFinalQuotation($quotation);

    return response()->json([
        'message' => 'Final quotation retrieved successfully.',
        'data' => $quotation
    ], 200);
}

private function loadLineItemsForFinalQuotation(Quotation $quotation): void
{
    if ($quotation->quotation_type !== 'final') {
        return;
    }

    $quotation->loadMissing(['lineItems.pricingItem']);
}

private function storeRules(): array
{
    return [
        'monthly_electric_bill' => 'bail|required|numeric|gt:0',
        'remarks' => 'nullable|string',
    ];
}

private function storeMessages(): array
{
    return [
        'monthly_electric_bill.required' => 'Monthly electric bill is required.',
        'monthly_electric_bill.numeric' => 'Monthly electric bill must be a valid number.',
        'monthly_electric_bill.gt' => 'Monthly electric bill must be greater than 0.',
    ];
}

private function updateRules(): array
{
    return [
        'quotation_type' => 'bail|nullable|in:initial,final',
        'panel_watts' => 'bail|nullable|numeric|min:1',
        'inverter_type' => 'bail|nullable|string|max:255',
        'battery_model' => 'bail|nullable|string|max:255',
        'battery_capacity_ah' => 'bail|nullable|numeric|min:0',
        'status' => 'bail|nullable|in:pending,approved,rejected,completed',
        'panel_cost' => 'bail|nullable|numeric|min:0',
        'inverter_cost' => 'bail|nullable|numeric|min:0',
        'battery_cost' => 'bail|nullable|numeric|min:0',
        'bos_cost' => 'bail|nullable|numeric|min:0',
        'materials_subtotal' => 'bail|nullable|numeric|min:0',
        'labor_cost' => 'bail|nullable|numeric|min:0',
        'project_cost' => 'bail|nullable|numeric|min:0',
        'remarks' => 'nullable|string',
    ];
}

private function updateMessages(): array
{
    return [
        'quotation_type.in' => 'Quotation type must be either initial or final.',
        'panel_watts.numeric' => 'Panel watts must be a valid number.',
        'panel_watts.min' => 'Panel watts must be at least 1.',
        'battery_capacity_ah.numeric' => 'Battery capacity Ah must be a valid number.',
        'battery_capacity_ah.min' => 'Battery capacity Ah must be at least 0.',
        'status.in' => 'Status must be pending, approved, rejected, or completed.',
        'panel_cost.numeric' => 'Panel cost must be a valid number.',
        'panel_cost.min' => 'Panel cost must be at least 0.',
        'inverter_cost.numeric' => 'Inverter cost must be a valid number.',
        'inverter_cost.min' => 'Inverter cost must be at least 0.',
        'battery_cost.numeric' => 'Battery cost must be a valid number.',
        'battery_cost.min' => 'Battery cost must be at least 0.',
        'bos_cost.numeric' => 'BOS cost must be a valid number.',
        'bos_cost.min' => 'BOS cost must be at least 0.',
        'materials_subtotal.numeric' => 'Materials subtotal must be a valid number.',
        'materials_subtotal.min' => 'Materials subtotal must be at least 0.',
        'labor_cost.numeric' => 'Labor cost must be a valid number.',
        'labor_cost.min' => 'Labor cost must be at least 0.',
        'project_cost.numeric' => 'Project cost must be a valid number.',
        'project_cost.min' => 'Project cost must be at least 0.',
    ];
}

private function storeFinalQuotationRules(): array
{
    return [
        'inspection_request_id' => 'bail|required|exists:inspection_requests,id',
        'monthly_electric_bill' => 'bail|required|numeric|gt:0',
        'rate_per_kwh' => 'bail|nullable|numeric|min:0',
        'days_in_month' => 'bail|nullable|integer|min:1',
        'sun_hours' => 'bail|nullable|numeric|min:0.1',
        'pv_safety_factor' => 'bail|nullable|numeric|min:0',
        'battery_factor' => 'bail|nullable|numeric|min:0',
        'battery_voltage' => 'bail|nullable|numeric|min:0.1',
        'pv_system_type' => [
            'bail',
            'required',
            'string',
            'max:255',
            Rule::in($this->finalQuotationOptionValues('system_types')),
        ],
        'with_battery' => 'bail|required|boolean',
        'inverter_type' => 'bail|nullable|string|max:255',
        'battery_model' => 'bail|nullable|string|max:255',
        'battery_capacity_ah' => 'bail|nullable|numeric|min:0',
        'panel_watts' => 'bail|nullable|numeric|min:1',
        'panel_cost' => 'bail|nullable|numeric|min:0',
        'inverter_cost' => 'bail|nullable|numeric|min:0',
        'battery_cost' => 'bail|nullable|numeric|min:0',
        'bos_cost' => 'bail|nullable|numeric|min:0',
        'materials_subtotal' => 'bail|nullable|numeric|min:0',
        'labor_cost' => 'bail|nullable|numeric|min:0',
        'project_cost' => 'bail|nullable|numeric|min:0',
        'status' => 'bail|nullable|in:pending,approved,rejected,completed',
        'remarks' => 'nullable|string',
    ];
}

private function storeFinalQuotationMessages(): array
{
    return [
        'inspection_request_id.required' => 'Inspection request is required.',
        'inspection_request_id.exists' => 'Selected inspection request does not exist.',
        'monthly_electric_bill.required' => 'Monthly electric bill is required.',
        'monthly_electric_bill.numeric' => 'Monthly electric bill must be a valid number.',
        'monthly_electric_bill.gt' => 'Monthly electric bill must be greater than 0.',
        'rate_per_kwh.numeric' => 'Rate per kWh must be a valid number.',
        'rate_per_kwh.min' => 'Rate per kWh must be at least 0.',
        'days_in_month.integer' => 'Days in month must be a whole number.',
        'days_in_month.min' => 'Days in month must be at least 1.',
        'sun_hours.numeric' => 'Sun hours must be a valid number.',
        'sun_hours.min' => 'Sun hours must be at least 0.1.',
        'pv_safety_factor.numeric' => 'PV safety factor must be a valid number.',
        'pv_safety_factor.min' => 'PV safety factor must be at least 0.',
        'battery_factor.numeric' => 'Battery factor must be a valid number.',
        'battery_factor.min' => 'Battery factor must be at least 0.',
        'battery_voltage.numeric' => 'Battery voltage must be a valid number.',
        'battery_voltage.min' => 'Battery voltage must be at least 0.1.',
        'pv_system_type.required' => 'PV system type is required.',
        'pv_system_type.string' => 'PV system type must be a valid string.',
        'pv_system_type.in' => 'PV system type must be one of the supported system types.',
        'with_battery.required' => 'Battery selection is required.',
        'with_battery.boolean' => 'Battery selection must be true or false.',
        'battery_capacity_ah.numeric' => 'Battery capacity Ah must be a valid number.',
        'battery_capacity_ah.min' => 'Battery capacity Ah must be at least 0.',
        'panel_watts.numeric' => 'Panel watts must be a valid number.',
        'panel_watts.min' => 'Panel watts must be at least 1.',
        'panel_cost.numeric' => 'Panel cost must be a valid number.',
        'panel_cost.min' => 'Panel cost must be at least 0.',
        'inverter_cost.numeric' => 'Inverter cost must be a valid number.',
        'inverter_cost.min' => 'Inverter cost must be at least 0.',
        'battery_cost.numeric' => 'Battery cost must be a valid number.',
        'battery_cost.min' => 'Battery cost must be at least 0.',
        'bos_cost.numeric' => 'BOS cost must be a valid number.',
        'bos_cost.min' => 'BOS cost must be at least 0.',
        'materials_subtotal.numeric' => 'Materials subtotal must be a valid number.',
        'materials_subtotal.min' => 'Materials subtotal must be at least 0.',
        'labor_cost.numeric' => 'Labor cost must be a valid number.',
        'labor_cost.min' => 'Labor cost must be at least 0.',
        'project_cost.numeric' => 'Project cost must be a valid number.',
        'project_cost.min' => 'Project cost must be at least 0.',
        'status.in' => 'Status must be pending, approved, rejected, or completed.',
    ];
}

private function finalQuotationOptions(): array
{
    return config('quotation_options.final_quotation', []);
}

private function finalQuotationOptionValues(string $key): array
{
    $values = [];
    $options = $this->finalQuotationOptions();

    foreach ($options[$key] ?? [] as $option) {
        if (array_key_exists('value', $option)) {
            $values[] = $option['value'];
        }
    }

    return $values;
}
}
