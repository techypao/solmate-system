<?php

namespace App\Services;

class QuotationComputationService
{
    public function estimatePackageProjectCost($systemKw, $pricePerKw): ?float
    {
        if ($systemKw <= 0 || $pricePerKw <= 0) {
            return null;
        }

        return round($systemKw * $pricePerKw, 2);
    }

    public function computeSizing(array $inputs): array
    {
        $monthlyElectricBill = $inputs['monthly_electric_bill'] ?? 0;
        $ratePerKwh = $inputs['rate_per_kwh'] ?? 14;
        $daysInMonth = $inputs['days_in_month'] ?? 30;
        $sunHours = $inputs['sun_hours'] ?? 4.5;
        $pvSafetyFactor = $inputs['pv_safety_factor'] ?? 1.8;
        $batteryFactor = $inputs['battery_factor'] ?? 1.0;
        $batteryVoltage = $inputs['battery_voltage'] ?? 51.2;
        $panelWatts = $inputs['panel_watts'] ?? 610;
        $withBattery = $inputs['with_battery'] ?? true;

        $monthlyKwh = $ratePerKwh > 0 ? $monthlyElectricBill / $ratePerKwh : 0;
        $dailyKwh = $daysInMonth > 0 ? $monthlyKwh / $daysInMonth : 0;
        $pvKwRaw = $sunHours > 0 ? $dailyKwh / $sunHours : 0;
        $pvKwSafe = $pvKwRaw * $pvSafetyFactor;
        $panelQuantity = $panelWatts > 0 ? ceil(($pvKwSafe * 1000) / $panelWatts) : 0;
        $systemKw = ($panelQuantity * $panelWatts) / 1000;
        $batteryRequiredKwh = $withBattery ? ($dailyKwh * $batteryFactor) : 0;
        $batteryRequiredAh = $batteryVoltage > 0 ? (($batteryRequiredKwh * 1000) / $batteryVoltage) : 0;

        return [
            'monthly_kwh' => round($monthlyKwh, 2),
            'daily_kwh' => round($dailyKwh, 2),
            'pv_kw_raw' => round($pvKwRaw, 2),
            'pv_kw_safe' => round($pvKwSafe, 2),
            'panel_quantity' => $panelQuantity,
            'system_kw' => round($systemKw, 2),
            'battery_required_kwh' => round($batteryRequiredKwh, 2),
            'battery_required_ah' => round($batteryRequiredAh, 2),
        ];
    }

    public function computeRoi($projectCost, $monthlyElectricBill): array
    {
        $estimatedMonthlySavings = null;
        $estimatedAnnualSavings = null;
        $roiYears = null;

        if (!is_null($projectCost) && $projectCost > 0 && $monthlyElectricBill > 0) {
            // Client-approved ROI method:
            // ROI_months = project_cost / monthly_bill
            // ROI_years = ROI_months / 12
            $estimatedMonthlySavings = round((float) $monthlyElectricBill, 2);
            $estimatedAnnualSavings = round($estimatedMonthlySavings * 12, 2);

            $roiMonths = $projectCost / $monthlyElectricBill;
            $roiYears = round($roiMonths / 12, 2);
        }

        return [
            'estimated_monthly_savings' => $estimatedMonthlySavings,
            'estimated_annual_savings' => $estimatedAnnualSavings,
            'roi_years' => $roiYears,
        ];
    }
}
