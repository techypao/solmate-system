<?php

namespace App\Services;

use App\Models\PricingItem;
use App\Models\Quotation;
use Illuminate\Support\Collection;

class PreInspectionQuotationOptionService
{
    public function __construct(private QuotationComputationService $quotationComputationService)
    {
    }

    public function replaceForInitialQuotation(
        Quotation $quotation,
        array $computedValues,
        float $baseProjectCost,
        float $monthlyElectricBill
    ): Quotation {
        $quotation->preInspectionOptions()->delete();

        $inverter = $this->selectNearestHighest(
            $this->inverterComponents(),
            (float) ($computedValues['system_kw'] ?? 0),
            'capacity_kw'
        );

        $options = [
            $this->buildOptionPayload(
                'on-grid',
                false,
                $computedValues,
                $baseProjectCost,
                $monthlyElectricBill,
                $inverter,
                null
            ),
            $this->buildOptionPayload(
                'hybrid',
                true,
                $computedValues,
                $baseProjectCost,
                $monthlyElectricBill,
                $inverter,
                $this->selectNearestHighest(
                    $this->batteryComponents(),
                    (float) ($computedValues['battery_required_ah'] ?? 0),
                    'capacity_ah'
                )
            ),
        ];

        foreach ($options as $option) {
            $quotation->preInspectionOptions()->create($option);
        }

        return $quotation->fresh(['preInspectionOptions']);
    }

    private function buildOptionPayload(
        string $systemType,
        bool $withBattery,
        array $computedValues,
        float $baseProjectCost,
        float $monthlyElectricBill,
        ?array $inverter,
        ?array $battery
    ): array {
        $inverterCost = $inverter ? (float) $inverter['price'] : 0.00;
        $batteryCost = $withBattery && $battery ? (float) $battery['price'] : 0.00;
        $projectCost = round($baseProjectCost + $inverterCost + $batteryCost, 2);
        $roiValues = $this->quotationComputationService->computeRoi($projectCost, $monthlyElectricBill);

        $notes = [];

        if (! $inverter) {
            $notes[] = 'No active inverter price was available, so inverter cost was not added.';
        } elseif (! empty($inverter['exceeds_available_capacity'])) {
            $notes[] = 'Computed system size exceeds the largest available inverter capacity; technician validation is required.';
        }

        if ($withBattery) {
            if (! $battery) {
                $notes[] = 'No active battery price was available, so battery cost was not added.';
            } elseif (! empty($battery['exceeds_available_capacity'])) {
                $notes[] = 'Computed battery requirement exceeds the largest available battery capacity; technician validation is required.';
            }
        }

        return [
            'system_type' => $systemType,
            'with_battery' => $withBattery,
            'system_kw' => $computedValues['system_kw'] ?? null,
            'panel_quantity' => $computedValues['panel_quantity'] ?? null,
            'panel_watts' => $computedValues['panel_watts'] ?? null,
            'base_project_cost' => $baseProjectCost,
            'inverter_capacity_kw' => $inverter['capacity_kw'] ?? null,
            'inverter_cost' => $inverterCost,
            'battery_required_kwh' => $withBattery ? ($computedValues['battery_required_kwh'] ?? null) : 0,
            'battery_required_ah' => $withBattery ? ($computedValues['battery_required_ah'] ?? null) : 0,
            'battery_capacity_ah' => $withBattery ? ($battery['capacity_ah'] ?? null) : null,
            'battery_voltage' => $withBattery ? ($battery['voltage'] ?? null) : null,
            'battery_cost' => $batteryCost,
            'project_cost' => $projectCost,
            'estimated_monthly_savings' => $roiValues['estimated_monthly_savings'],
            'estimated_annual_savings' => $roiValues['estimated_annual_savings'],
            'roi_years' => $roiValues['roi_years'],
            'requires_technician_validation' => $notes !== [],
            'validation_note' => $notes !== [] ? implode(' ', $notes) : null,
        ];
    }

    private function selectNearestHighest(Collection $components, float $requiredCapacity, string $capacityKey): ?array
    {
        if ($components->isEmpty()) {
            return null;
        }

        $sorted = $components
            ->sortBy([
                [$capacityKey, 'asc'],
                ['price', 'asc'],
            ])
            ->values();

        $selected = $sorted->first(fn (array $component): bool => (float) $component[$capacityKey] >= $requiredCapacity);

        if ($selected) {
            return $selected;
        }

        $largest = $sorted->last();
        $largest['exceeds_available_capacity'] = true;

        return $largest;
    }

    private function inverterComponents(): Collection
    {
        return $this->componentCatalog(
            'inverter',
            'capacity_kw',
            fn (PricingItem $item): ?float => $this->extractKw($item)
        )->merge($this->fallbackComponents('fallback_inverters', 'capacity_kw'));
    }

    private function batteryComponents(): Collection
    {
        return $this->componentCatalog(
            'battery',
            'capacity_ah',
            fn (PricingItem $item): ?float => $this->extractAh($item)
        )->merge($this->fallbackComponents('fallback_batteries', 'capacity_ah'));
    }

    private function componentCatalog(string $category, string $capacityKey, callable $extractCapacity): Collection
    {
        return PricingItem::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->get()
            ->toBase()
            ->map(function (PricingItem $item) use ($capacityKey, $extractCapacity): ?array {
                $capacity = $extractCapacity($item);

                if ($capacity === null || $capacity <= 0) {
                    return null;
                }

                return [
                    $capacityKey => $capacity,
                    'voltage' => $this->extractVoltage($item),
                    'price' => (float) $item->default_unit_price,
                ];
            })
            ->filter()
            ->values();
    }

    private function fallbackComponents(string $configKey, string $capacityKey): Collection
    {
        return collect(config("pre_inspection_components.{$configKey}", []))
            ->map(function (array $component) use ($capacityKey): ?array {
                if (! isset($component[$capacityKey], $component['price'])) {
                    return null;
                }

                return [
                    $capacityKey => (float) $component[$capacityKey],
                    'voltage' => isset($component['voltage']) ? (float) $component['voltage'] : null,
                    'price' => (float) $component['price'],
                ];
            })
            ->filter()
            ->values();
    }

    private function extractKw(PricingItem $item): ?float
    {
        return $this->extractLargestUnitValue($this->componentSearchText($item), 'kw');
    }

    private function extractAh(PricingItem $item): ?float
    {
        return $this->extractLargestUnitValue($this->componentSearchText($item), 'ah');
    }

    private function extractVoltage(PricingItem $item): ?float
    {
        return $this->extractLargestUnitValue($this->componentSearchText($item), 'v');
    }

    private function extractLargestUnitValue(string $text, string $unit): ?float
    {
        preg_match_all('/(\d+(?:\.\d+)?)\s*' . preg_quote($unit, '/') . '\b/i', $text, $matches);

        if (empty($matches[1])) {
            return null;
        }

        return max(array_map('floatval', $matches[1]));
    }

    private function componentSearchText(PricingItem $item): string
    {
        return implode(' ', array_filter([
            $item->name,
            $item->brand,
            $item->model,
            $item->specification,
        ]));
    }
}
