<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection-Based Quotation #{{ $quotation->id }}</title>
    <style>
        @page {
            margin: 28px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0F2F4A;
            font-size: 12px;
            line-height: 1.45;
        }

        .brand-header {
            padding: 18px 20px;
            border: 1px solid #d7e2ee;
            border-radius: 14px;
            background: #123A5A;
            color: #ffffff;
            margin-bottom: 18px;
        }

        .brand-name {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .brand-name .accent {
            color: #F4D000;
        }

        .brand-subtitle {
            margin: 6px 0 0;
            font-size: 12px;
            color: #DDE7EE;
        }

        .section {
            margin-bottom: 18px;
            padding: 16px 18px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #ffffff;
        }

        .section-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 700;
            color: #123A5A;
        }

        .meta-table,
        .detail-table,
        .cost-table,
        .line-item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td,
        .detail-table td,
        .cost-table td,
        .line-item-table th,
        .line-item-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .meta-table tr:not(:last-child) td,
        .detail-table tr:not(:last-child) td,
        .cost-table tr:not(:last-child) td,
        .line-item-table thead th,
        .line-item-table tbody tr:not(:last-child) td {
            border-bottom: 1px solid #edf2f7;
        }

        .label {
            width: 34%;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: #5E7288;
        }

        .value {
            font-size: 12px;
            font-weight: 600;
            color: #123A5A;
        }

        .grid {
            width: 100%;
        }

        .grid td {
            width: 50%;
            padding-right: 8px;
            vertical-align: top;
        }

        .highlight-box {
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #123A5A;
            color: #ffffff;
        }

        .highlight-label {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #DDE7EE;
        }

        .highlight-value {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #E6C200;
        }

        .savings-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .savings-card {
            padding: 12px 14px;
            border: 1px solid #DDE7EE;
            border-radius: 12px;
            background: #f8fafc;
        }

        .savings-title {
            margin: 0 0 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #5E7288;
        }

        .savings-value {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #123A5A;
        }

        .line-item-table th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: left;
            color: #5E7288;
        }

        .line-item-table td:last-child,
        .cost-table td:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .muted {
            color: #5E7288;
        }

        .remarks-box {
            padding: 14px 16px;
            border: 1px solid #f4d58a;
            border-radius: 12px;
            background: #fffbeb;
            white-space: pre-wrap;
        }

        .footer-note {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
            color: #5E7288;
        }
    </style>
</head>
<body>
@php
    $createdDate = $quotation->created_at ? $quotation->created_at->format('F j, Y') : '—';
    $technicianName = $technician?->name;
    $fmtPeso = fn ($value) => ($value === null || $value === '') ? '—' : 'PHP ' . number_format((float) $value, 2);
    $fmtNumber = fn ($value, $suffix = '') => ($value === null || $value === '') ? '—' : number_format((float) $value, 2) . $suffix;
    $systemType = $quotation->pv_system_type ? ucwords(str_replace('_', ' ', $quotation->pv_system_type)) : '—';
    $inverterType = $quotation->inverter_type ? ucwords(str_replace('_', ' ', $quotation->inverter_type)) : '—';
    $appliedPromo = $quotation->appliedPromo;
    $hasAppliedPromo = $appliedPromo || $quotation->applied_promo_id || (float) ($quotation->promo_discount ?? 0) > 0;
    $promoLabel = '—';

    if ($appliedPromo) {
        $promoLabel = $appliedPromo->title ?: ($appliedPromo->free_item_description ?: 'Promo #' . $appliedPromo->id);
        $conditions = $appliedPromo->conditions ?? [];
        $appliesTo = isset($conditions['applies_to']) ? ucwords(str_replace('_', ' ', (string) $conditions['applies_to'])) : '';
        $minQty = (int) ($conditions['min_qty'] ?? 0);
        $freeQty = (int) ($conditions['free_qty'] ?? 1);

        if ($appliedPromo->promo_type === 'free_item' && $appliesTo !== '' && $minQty > 0) {
            $promoLabel .= ' (' . $appliesTo . ': buy ' . $minQty . ', get ' . $freeQty . ' free)';
        } elseif ($appliedPromo->promo_type === 'free_item' && $appliedPromo->free_item_description) {
            $promoLabel .= ' (' . $appliedPromo->free_item_description . ')';
        }
    } elseif ($quotation->applied_promo_id) {
        $promoLabel = 'Promo #' . $quotation->applied_promo_id;
    }
@endphp

<div class="brand-header">
    <p class="brand-name">Sol<span class="accent">Mate</span></p>
    <p class="brand-subtitle">Inspection-based solar quotation summary and cost breakdown</p>
</div>

<div class="section">
    <p class="section-title">Quotation Information</p>
    <table class="grid">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="label">Quotation ID</td><td class="value">#{{ $quotation->id }}</td></tr>
                    <tr><td class="label">Quotation Type</td><td class="value">Inspection-Based</td></tr>
                    <tr><td class="label">Created Date</td><td class="value">{{ $createdDate }}</td></tr>
                </table>
            </td>
            <td>
                <table class="meta-table">
                    <tr><td class="label">Customer Name</td><td class="value">{{ $customer?->name ?? '—' }}</td></tr>
                    <tr><td class="label">Customer Email</td><td class="value">{{ $customer?->email ?? '—' }}</td></tr>
                    <tr><td class="label">Technician Name</td><td class="value">{{ $technicianName ?: '—' }}</td></tr>
                    <tr><td class="label">Inspection Request</td><td class="value">{{ $inspectionRequest?->id ? '#' . $inspectionRequest->id : '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="highlight-box">
        <p class="highlight-label">Total Project Cost</p>
        <p class="highlight-value">{{ $fmtPeso($quotation->project_cost) }}</p>
    </div>
</div>

<div class="section">
    <p class="section-title">Inspection-Based Quotation Details</p>
    <table class="grid">
        <tr>
            <td>
                <table class="detail-table">
                    <tr><td class="label">Monthly Electric Bill</td><td class="value">{{ $fmtPeso($quotation->monthly_electric_bill) }}</td></tr>
                    <tr><td class="label">System Size</td><td class="value">{{ $fmtNumber($quotation->system_kw, ' kW') }}</td></tr>
                    <tr><td class="label">Panel Quantity</td><td class="value">{{ $quotation->panel_quantity ?? '—' }}</td></tr>
                    <tr><td class="label">Panel Watts</td><td class="value">{{ $quotation->panel_watts ? number_format((float) $quotation->panel_watts, 0) . ' W' : '—' }}</td></tr>
                    <tr><td class="label">System Type</td><td class="value">{{ $systemType }}</td></tr>
                </table>
            </td>
            <td>
                <table class="detail-table">
                    <tr><td class="label">Inverter Details</td><td class="value">{{ $inverterType }}</td></tr>
                    <tr><td class="label">Battery Included</td><td class="value">{{ $quotation->with_battery ? 'Yes' : 'No' }}</td></tr>
                    <tr><td class="label">Battery Model</td><td class="value">{{ $quotation->battery_model ?: '—' }}</td></tr>
                    <tr><td class="label">Battery Capacity</td><td class="value">{{ $quotation->battery_capacity_ah ? number_format((float) $quotation->battery_capacity_ah, 2) . ' Ah' : '—' }}</td></tr>
                    <tr><td class="label">Required Battery Size</td><td class="value">{{ $quotation->battery_required_kwh ? number_format((float) $quotation->battery_required_kwh, 2) . ' kWh' : '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <p class="section-title">Cost Breakdown</p>
    <table class="cost-table">
        <tr><td class="label">Panel Cost</td><td class="value">{{ $fmtPeso($quotation->panel_cost) }}</td></tr>
        <tr><td class="label">Inverter Cost</td><td class="value">{{ $fmtPeso($quotation->inverter_cost) }}</td></tr>
        <tr><td class="label">Battery Cost</td><td class="value">{{ $fmtPeso($quotation->battery_cost) }}</td></tr>
        <tr><td class="label">BOS Cost</td><td class="value">{{ $fmtPeso($quotation->bos_cost) }}</td></tr>
        <tr><td class="label">Materials Subtotal</td><td class="value">{{ $fmtPeso($quotation->materials_subtotal) }}</td></tr>
        <tr><td class="label">Labor Cost</td><td class="value">{{ $fmtPeso($quotation->labor_cost) }}</td></tr>
        @if($hasAppliedPromo)
            <tr><td class="label">Applied Promo</td><td class="value">{{ $promoLabel }}</td></tr>
            @if((float) ($quotation->promo_discount ?? 0) > 0)
                <tr><td class="label">Promo Discount</td><td class="value">-{{ $fmtPeso($quotation->promo_discount) }}</td></tr>
            @endif
        @endif
        <tr><td class="label">Total Project Cost</td><td class="value">{{ $fmtPeso($quotation->project_cost) }}</td></tr>
    </table>

    @if($lineItems->isNotEmpty())
        <p class="section-title" style="margin-top:16px;">Pricing Items</p>
        <table class="line-item-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lineItems as $lineItem)
                    <tr>
                        <td>{{ $lineItem->description ?: ($lineItem->pricingItem->name ?? '—') }}</td>
                        <td>{{ $lineItem->category ? ucwords(str_replace('_', ' ', $lineItem->category)) : '—' }}</td>
                        <td>{{ $lineItem->qty ?? 1 }} {{ $lineItem->unit ?? '' }}</td>
                        <td>{{ $fmtPeso($lineItem->total_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="section">
    <p class="section-title">Estimated Savings & ROI</p>
    <table class="savings-grid">
        <tr>
            <td>
                <div class="savings-card">
                    <p class="savings-title">Estimated Monthly Savings</p>
                    <p class="savings-value">{{ $fmtPeso($quotation->estimated_monthly_savings) }}</p>
                </div>
            </td>
            <td>
                <div class="savings-card">
                    <p class="savings-title">Estimated Annual Savings</p>
                    <p class="savings-value">{{ $fmtPeso($quotation->estimated_annual_savings) }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="savings-card">
                    <p class="savings-title">Return on Investment</p>
                    <p class="savings-value">{{ $quotation->roi_years ? number_format((float) $quotation->roi_years, 1) . ' years' : '—' }}</p>
                    <p class="muted" style="margin:8px 0 0;">Estimated payback period based on current usage and projected savings.</p>
                </div>
            </td>
        </tr>
    </table>
</div>

@if(filled($quotation->remarks))
    <div class="section">
        <p class="section-title">Remarks / Notes</p>
        <div class="remarks-box">{{ $quotation->remarks }}</div>
    </div>
@endif

<p class="footer-note">Generated from the SolMate customer portal.</p>
</body>
</html>
