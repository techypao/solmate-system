<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
}
