<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\ServiceRequest;
use App\Models\InspectionRequest;

class QuotationController extends Controller
{
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
            $validated = $request->validate([
                'monthly_electric_bill' => 'required|numeric|min:0',
                'remarks' => 'nullable|string',
            ], [
                'monthly_electric_bill.required' => 'Monthly electric bill is required.',
                'monthly_electric_bill.numeric' => 'Monthly electric bill must be a valid number.',
            ]);

            $monthlyElectricBill = $validated['monthly_electric_bill'];
            $ratePerKwh = 14;
            $daysInMonth = 30;
            $sunHours = 4.5;
            $pvSafetyFactor = 1.8;
            $batteryFactor = 1.0;
            $batteryVoltage = 51.2;
            $panelWatts = 610;
            $withBattery = true;

            $monthlyKwh = $ratePerKwh > 0 ? $monthlyElectricBill / $ratePerKwh : 0;
            $dailyKwh = $daysInMonth > 0 ? $monthlyKwh / $daysInMonth : 0;
            $pvKwRaw = $sunHours > 0 ? $dailyKwh / $sunHours : 0;
            $pvKwSafe = $pvKwRaw * $pvSafetyFactor;
            $panelQuantity = $panelWatts > 0 ? ceil(($pvKwSafe * 1000) / $panelWatts) : 0;
            $systemKw = ($panelQuantity * $panelWatts) / 1000;

            $batteryRequiredKwh = $withBattery ? ($dailyKwh * $batteryFactor) : 0;
            $batteryRequiredAh = $batteryVoltage > 0 ? (($batteryRequiredKwh * 1000) / $batteryVoltage) : 0;

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
                'pv_system_type' => 'hybrid',
                'with_battery' => $withBattery,
                'inverter_type' => null,
                'battery_model' => null,
                'battery_capacity_ah' => null,
                'panel_watts' => $panelWatts,
                'monthly_kwh' => round($monthlyKwh, 2),
                'daily_kwh' => round($dailyKwh, 2),
                'pv_kw_raw' => round($pvKwRaw, 2),
                'pv_kw_safe' => round($pvKwSafe, 2),
                'panel_quantity' => $panelQuantity,
                'system_kw' => round($systemKw, 2),
                'battery_required_kwh' => round($batteryRequiredKwh, 2),
                'battery_required_ah' => round($batteryRequiredAh, 2),
                'panel_cost' => null,
                'inverter_cost' => null,
                'battery_cost' => null,
                'bos_cost' => null,
                'materials_subtotal' => null,
                'labor_cost' => null,
                'project_cost' => null,
                'estimated_monthly_savings' => null,
'estimated_annual_savings' => null,
'roi_years' => null,
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

        return response()->json($quotation);
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

        $validated = $request->validate([
            'quotation_type' => 'nullable|in:initial,final',
            'panel_watts' => 'nullable|numeric|min:1',
            'inverter_type' => 'nullable|string|max:255',
            'battery_model' => 'nullable|string|max:255',
            'battery_capacity_ah' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,approved,rejected,completed',
            'panel_cost' => 'nullable|numeric|min:0',
            'inverter_cost' => 'nullable|numeric|min:0',
            'battery_cost' => 'nullable|numeric|min:0',
            'bos_cost' => 'nullable|numeric|min:0',
            'materials_subtotal' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'project_cost' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ], [
            'quotation_type.in' => 'Quotation type must be either initial or final.',
            'panel_watts.numeric' => 'Panel watts must be a valid number.',
            'panel_watts.min' => 'Panel watts must be at least 1.',
            'battery_capacity_ah.numeric' => 'Battery capacity Ah must be a valid number.',
            'status.in' => 'Status must be pending, approved, rejected, or completed.',
            'panel_cost.numeric' => 'Panel cost must be a valid number.',
            'inverter_cost.numeric' => 'Inverter cost must be a valid number.',
            'battery_cost.numeric' => 'Battery cost must be a valid number.',
            'bos_cost.numeric' => 'BOS cost must be a valid number.',
            'materials_subtotal.numeric' => 'Materials subtotal must be a valid number.',
            'labor_cost.numeric' => 'Labor cost must be a valid number.',
            'project_cost.numeric' => 'Project cost must be a valid number.',
        ]);

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

        $monthlyKwh = $ratePerKwh > 0 ? $monthlyElectricBill / $ratePerKwh : 0;
        $dailyKwh = $daysInMonth > 0 ? $monthlyKwh / $daysInMonth : 0;
        $pvKwRaw = $sunHours > 0 ? $dailyKwh / $sunHours : 0;
        $pvKwSafe = $pvKwRaw * $pvSafetyFactor;
        $panelQuantity = $panelWatts > 0 ? ceil(($pvKwSafe * 1000) / $panelWatts) : 0;
        $systemKw = ($panelQuantity * $panelWatts) / 1000;
        $batteryRequiredKwh = $withBattery ? ($dailyKwh * $batteryFactor) : 0;
        $batteryRequiredAh = $batteryVoltage > 0 ? (($batteryRequiredKwh * 1000) / $batteryVoltage) : 0;

        $projectCost = $validated['project_cost'] ?? $quotation->project_cost;
$estimatedMonthlySavings = null;
$estimatedAnnualSavings = null;
$roiYears = null;

// Compute ROI only when final quotation has a valid project cost
if (!is_null($projectCost) && $projectCost > 0 && $monthlyElectricBill > 0) {
    $estimatedMonthlySavings = $monthlyElectricBill * 0.3; // temporary assumption
    $estimatedAnnualSavings = $estimatedMonthlySavings * 12;

    if ($estimatedAnnualSavings > 0) {
        $roiYears = round($projectCost / $estimatedAnnualSavings, 2);
    }
}

        $quotation->update(array_merge($validated, [
            'monthly_kwh' => round($monthlyKwh, 2),
            'daily_kwh' => round($dailyKwh, 2),
            'pv_kw_raw' => round($pvKwRaw, 2),
            'pv_kw_safe' => round($pvKwSafe, 2),
            'panel_quantity' => $panelQuantity,
            'system_kw' => round($systemKw, 2),
            'battery_required_kwh' => round($batteryRequiredKwh, 2),
            'battery_required_ah' => round($batteryRequiredAh, 2),

            'estimated_monthly_savings' => $estimatedMonthlySavings,
    'estimated_annual_savings' => $estimatedAnnualSavings,
    'roi_years' => $roiYears,
            
        ]));

        return response()->json([
            'message' => 'Quotation updated successfully',
            'data' => $quotation,
        ]);
    }
public function storeFinalQuotation(Request $request)
{
    $validated = $request->validate([
        'inspection_request_id' => 'required|exists:inspection_requests,id',
        'monthly_electric_bill' => 'required|numeric|min:0',
        'rate_per_kwh' => 'nullable|numeric|min:0',
        'days_in_month' => 'nullable|integer|min:1',
        'sun_hours' => 'nullable|numeric|min:0.1',
        'pv_safety_factor' => 'nullable|numeric|min:0',
        'battery_factor' => 'nullable|numeric|min:0',
        'battery_voltage' => 'nullable|numeric|min:0.1',
        'pv_system_type' => 'required|string|max:255',
        'with_battery' => 'required|boolean',
        'inverter_type' => 'nullable|string|max:255',
        'battery_model' => 'nullable|string|max:255',
        'battery_capacity_ah' => 'nullable|numeric|min:0',
        'panel_watts' => 'nullable|numeric|min:1',
        'panel_cost' => 'nullable|numeric|min:0',
        'inverter_cost' => 'nullable|numeric|min:0',
        'battery_cost' => 'nullable|numeric|min:0',
        'bos_cost' => 'nullable|numeric|min:0',
        'materials_subtotal' => 'nullable|numeric|min:0',
        'labor_cost' => 'nullable|numeric|min:0',
        'project_cost' => 'nullable|numeric|min:0',
        'status' => 'nullable|in:pending,approved,rejected,completed',
        'remarks' => 'nullable|string',
    ]);

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
    $ratePerKwh = $validated['rate_per_kwh'] ?? 14;
    $daysInMonth = $validated['days_in_month'] ?? 30;
    $sunHours = $validated['sun_hours'] ?? 4.5;
    $pvSafetyFactor = $validated['pv_safety_factor'] ?? 1.8;
    $batteryFactor = $validated['battery_factor'] ?? 1.0;
    $batteryVoltage = $validated['battery_voltage'] ?? 51.2;
    $panelWatts = $validated['panel_watts'] ?? 610;
    $withBattery = $validated['with_battery'];

    $monthlyKwh = $ratePerKwh > 0 ? $monthlyElectricBill / $ratePerKwh : 0;
    $dailyKwh = $daysInMonth > 0 ? $monthlyKwh / $daysInMonth : 0;
    $pvKwRaw = $sunHours > 0 ? $dailyKwh / $sunHours : 0;
    $pvKwSafe = $pvKwRaw * $pvSafetyFactor;
    $panelQuantity = $panelWatts > 0 ? ceil(($pvKwSafe * 1000) / $panelWatts) : 0;
    $systemKw = ($panelQuantity * $panelWatts) / 1000;
    $batteryRequiredKwh = $withBattery ? ($dailyKwh * $batteryFactor) : 0;
    $batteryRequiredAh = $batteryVoltage > 0 ? (($batteryRequiredKwh * 1000) / $batteryVoltage) : 0;

    $projectCost = $validated['project_cost'] ?? null;
    $estimatedMonthlySavings = null;
    $estimatedAnnualSavings = null;
    $roiYears = null;

    if (!is_null($projectCost) && $projectCost > 0 && $monthlyElectricBill > 0) {
        $estimatedMonthlySavings = $monthlyElectricBill * 0.3;
        $estimatedAnnualSavings = $estimatedMonthlySavings * 12;

        if ($estimatedAnnualSavings > 0) {
            $roiYears = round($projectCost / $estimatedAnnualSavings, 2);
        }
    }

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
        'monthly_kwh' => round($monthlyKwh, 2),
        'daily_kwh' => round($dailyKwh, 2),
        'pv_kw_raw' => round($pvKwRaw, 2),
        'pv_kw_safe' => round($pvKwSafe, 2),
        'panel_quantity' => $panelQuantity,
        'system_kw' => round($systemKw, 2),
        'battery_required_kwh' => round($batteryRequiredKwh, 2),
        'battery_required_ah' => round($batteryRequiredAh, 2),
        'panel_cost' => $validated['panel_cost'] ?? null,
        'inverter_cost' => $validated['inverter_cost'] ?? null,
        'battery_cost' => $validated['battery_cost'] ?? null,
        'bos_cost' => $validated['bos_cost'] ?? null,
        'materials_subtotal' => $validated['materials_subtotal'] ?? null,
        'labor_cost' => $validated['labor_cost'] ?? null,
        'project_cost' => $validated['project_cost'] ?? null,
        'estimated_monthly_savings' => $estimatedMonthlySavings,
        'estimated_annual_savings' => $estimatedAnnualSavings,
        'roi_years' => $roiYears,
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

    return response()->json([
        'message' => 'Final quotation retrieved successfully.',
        'data' => $quotation
    ], 200);
}
}
