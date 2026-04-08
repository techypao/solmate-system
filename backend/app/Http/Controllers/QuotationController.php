<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with('user')->latest()->get();

        return response()->json($quotations);
    }

    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'quotation_type' => 'required|in:initial,final',
            'monthly_electric_bill' => 'nullable|numeric|min:0',
            'rate_per_kwh' => 'nullable|numeric|min:0',
            'days_in_month' => 'nullable|integer|min:1',
            'sun_hours' => 'nullable|numeric|min:0',
            'pv_safety_factor' => 'nullable|numeric|min:0',
            'battery_factor' => 'nullable|numeric|min:0',
            'battery_voltage' => 'nullable|numeric|min:0',
            'pv_system_type' => 'nullable|in:hybrid,on-grid,off-grid',
            'with_battery' => 'nullable|boolean',
            'inverter_type' => 'nullable|string|max:255',
            'battery_model' => 'nullable|string|max:255',
            'battery_capacity_ah' => 'nullable|numeric|min:0',
            'panel_watts' => 'nullable|numeric|min:1',
            'remarks' => 'nullable|string',
        ], [
            'quotation_type.required' => 'Quotation type is required.',
            'quotation_type.in' => 'Quotation type must be either initial or final.',
            'monthly_electric_bill.numeric' => 'Monthly electric bill must be a valid number.',
            'rate_per_kwh.numeric' => 'Rate per kWh must be a valid number.',
            'days_in_month.integer' => 'Days in month must be a whole number.',
            'days_in_month.min' => 'Days in month must be at least 1.',
            'sun_hours.numeric' => 'Sun hours must be a valid number.',
            'pv_safety_factor.numeric' => 'PV safety factor must be a valid number.',
            'battery_factor.numeric' => 'Battery factor must be a valid number.',
            'battery_voltage.numeric' => 'Battery voltage must be a valid number.',
            'pv_system_type.in' => 'PV system type must be hybrid, on-grid, or off-grid.',
            'with_battery.boolean' => 'With battery must be true or false.',
            'battery_capacity_ah.numeric' => 'Battery capacity Ah must be a valid number.',
            'panel_watts.numeric' => 'Panel watts must be a valid number.',
            'panel_watts.min' => 'Panel watts must be at least 1.',
        ]);

        $monthlyElectricBill = $validated['monthly_electric_bill'] ?? 0;
        $ratePerKwh = $validated['rate_per_kwh'] ?? 14;
        $daysInMonth = $validated['days_in_month'] ?? 30;
        $sunHours = $validated['sun_hours'] ?? 4.5;
        $pvSafetyFactor = $validated['pv_safety_factor'] ?? 1.8;
        $batteryFactor = $validated['battery_factor'] ?? 1.0;
        $batteryVoltage = $validated['battery_voltage'] ?? 51.2;
        $panelWatts = $validated['panel_watts'] ?? 610;
        $withBattery = $validated['with_battery'] ?? true;

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
            'quotation_type' => $validated['quotation_type'],
            'monthly_electric_bill' => $monthlyElectricBill,
            'rate_per_kwh' => $ratePerKwh,
            'days_in_month' => $daysInMonth,
            'sun_hours' => $sunHours,
            'pv_safety_factor' => $pvSafetyFactor,
            'battery_factor' => $batteryFactor,
            'battery_voltage' => $batteryVoltage,
            'pv_system_type' => $validated['pv_system_type'] ?? null,
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
            'panel_cost' => null,
            'inverter_cost' => null,
            'battery_cost' => null,
            'bos_cost' => null,
            'materials_subtotal' => null,
            'labor_cost' => null,
            'project_cost' => null,
            'status' => 'pending',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'message' => 'Quotation created successfully',
            'data' => $quotation,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    }
}
}