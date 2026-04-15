<?php

namespace App\Services;

use App\Models\QuotationSetting;

class QuotationSettingsService
{
    public function editableFields(): array
    {
        return array_keys($this->defaultValues());
    }

    public function defaultValues(): array
    {
        return config('quotation_settings.defaults', [
            'rate_per_kwh' => 14.00,
            'days_in_month' => 30,
            'sun_hours' => 4.50,
            'pv_safety_factor' => 1.80,
            'battery_factor' => 1.00,
            'battery_voltage' => 51.20,
            'labor_percentage' => 0.00,
            'default_bos_cost' => 0.00,
            'default_misc_cost' => 0.00,
            'default_panel_watts' => 610.00,
            'initial_price_per_kw' => 50000.00,
        ]);
    }

    public function current(): array
    {
        $settings = QuotationSetting::query()->first();

        if (!$settings) {
            return $this->defaultValues();
        }

        return array_merge($this->defaultValues(), $settings->only(array_keys($this->defaultValues())));
    }

    public function model(): ?QuotationSetting
    {
        return QuotationSetting::query()->first();
    }

    public function initialize(): QuotationSetting
    {
        $existingSettings = QuotationSetting::query()->first();

        if ($existingSettings) {
            return $existingSettings;
        }

        return QuotationSetting::query()->create($this->defaultValues());
    }

    public function update(array $attributes): QuotationSetting
    {
        $settings = $this->initialize();

        $settings->fill($this->filterEditableAttributes($attributes));
        $settings->save();

        return $settings->fresh();
    }

    public function filterEditableAttributes(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip($this->editableFields()));
    }
}
