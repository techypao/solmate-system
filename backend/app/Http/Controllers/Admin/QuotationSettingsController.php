<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QuotationSettingsService;
use Illuminate\Http\Request;

class QuotationSettingsController extends Controller
{
    public function __construct(private QuotationSettingsService $quotationSettingsService)
    {
    }

    public function show()
    {
        $settings = $this->quotationSettingsService->initialize();

        return response()->json([
            'message' => 'Quotation settings retrieved successfully.',
            'data' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $settings = $this->quotationSettingsService->update($validated);

        return response()->json([
            'message' => 'Quotation settings updated successfully.',
            'data' => $settings,
        ]);
    }

    private function rules(): array
    {
        return [
            'rate_per_kwh' => 'sometimes|numeric|min:0',
            'days_in_month' => 'sometimes|integer|min:1',
            'sun_hours' => 'sometimes|numeric|min:0.1',
            'pv_safety_factor' => 'sometimes|numeric|min:0',
            'battery_factor' => 'sometimes|numeric|min:0',
            'battery_voltage' => 'sometimes|numeric|min:0.1',
            'labor_percentage' => 'sometimes|numeric|min:0',
            'default_bos_cost' => 'sometimes|numeric|min:0',
            'default_misc_cost' => 'sometimes|numeric|min:0',
            'default_panel_watts' => 'sometimes|numeric|min:1',
            'initial_price_per_kw' => 'sometimes|numeric|min:0',
        ];
    }

    private function messages(): array
    {
        return [
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
            'labor_percentage.numeric' => 'Labor percentage must be a valid number.',
            'labor_percentage.min' => 'Labor percentage must be at least 0.',
            'default_bos_cost.numeric' => 'Default BOS cost must be a valid number.',
            'default_bos_cost.min' => 'Default BOS cost must be at least 0.',
            'default_misc_cost.numeric' => 'Default misc cost must be a valid number.',
            'default_misc_cost.min' => 'Default misc cost must be at least 0.',
            'default_panel_watts.numeric' => 'Default panel watts must be a valid number.',
            'default_panel_watts.min' => 'Default panel watts must be at least 1.',
            'initial_price_per_kw.numeric' => 'Initial quotation price per kW must be a valid number.',
            'initial_price_per_kw.min' => 'Initial quotation price per kW must be at least 0.',
        ];
    }
}
