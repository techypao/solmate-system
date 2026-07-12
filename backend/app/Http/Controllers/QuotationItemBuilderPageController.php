<?php

namespace App\Http\Controllers;

use App\Models\PricingItem;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;

class QuotationItemBuilderPageController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user?->role === User::ROLE_TECHNICIAN
                || ($user?->role === User::ROLE_ADMIN && $user->hasAdminPermission(User::PERMISSION_USE_ITEM_BUILDER)),
            403
        );

        return view('quotations.item-builder', [
            'categories' => PricingItem::CATEGORIES,
            'initialQuotationId' => $request->query('quotation_id'),
            'availableQuotations' => Quotation::query()
                ->with(['customer:id,name', 'inspectionRequest:id,status'])
                ->where('quotation_type', 'final')
                ->latest()
                ->limit(10)
                ->get(['id', 'user_id', 'inspection_request_id', 'quotation_type', 'status', 'created_at']),
        ]);
    }
}
