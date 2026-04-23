<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\QuotationSettingsService;
use Illuminate\Http\Request;

class QuotationSettingsPageController extends Controller
{
    public function __construct(private QuotationSettingsService $quotationSettingsService)
    {
    }

    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.quotation-settings', [
            'fields' => [
                'rate_per_kwh'         => ['label' => 'Rate per kWh',                     'step' => '0.01', 'min' => '0'],
                'days_in_month'        => ['label' => 'Days in month',                    'step' => '1',    'min' => '1'],
                'sun_hours'            => ['label' => 'Sun hours',                        'step' => '0.01', 'min' => '0.1'],
                'pv_safety_factor'     => ['label' => 'PV safety factor',                 'step' => '0.01', 'min' => '0'],
                'battery_factor'       => ['label' => 'Battery factor',                   'step' => '0.01', 'min' => '0'],
                'battery_voltage'      => ['label' => 'Battery voltage',                  'step' => '0.01', 'min' => '0.1'],
                'labor_percentage'     => ['label' => 'Labor percentage (%)',              'step' => '0.01', 'min' => '0'],
                'default_bos_cost'     => ['label' => 'Default BOS cost',                 'step' => '0.01', 'min' => '0'],
                'default_misc_cost'    => ['label' => 'Default misc cost',                'step' => '0.01', 'min' => '0'],
                'default_panel_watts'  => ['label' => 'Default panel watts',              'step' => '0.01', 'min' => '1'],
                'initial_price_per_kw' => ['label' => 'Initial quotation price per kW',   'step' => '0.01', 'min' => '0'],
            ],
            'defaults' => $this->quotationSettingsService->defaultValues(),
        ]);
    }
}
