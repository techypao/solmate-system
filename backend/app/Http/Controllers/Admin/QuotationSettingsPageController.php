<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class QuotationSettingsPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.quotation-settings', [
            'fields' => [
                'rate_per_kwh' => 'Rate per kWh',
                'days_in_month' => 'Days in month',
                'sun_hours' => 'Sun hours',
                'pv_safety_factor' => 'PV safety factor',
                'battery_factor' => 'Battery factor',
                'battery_voltage' => 'Battery voltage',
                'labor_percentage' => 'Labor percentage',
                'default_bos_cost' => 'Default BOS cost',
                'default_misc_cost' => 'Default misc cost',
                'default_panel_watts' => 'Default panel watts',
                'initial_price_per_kw' => 'Initial quotation price per kW',
            ],
        ]);
    }
}
