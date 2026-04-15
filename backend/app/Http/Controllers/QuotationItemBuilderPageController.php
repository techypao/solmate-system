<?php

namespace App\Http\Controllers;

use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Http\Request;

class QuotationItemBuilderPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless(
            in_array($request->user()?->role, [User::ROLE_ADMIN, User::ROLE_TECHNICIAN], true),
            403
        );

        return view('quotations.item-builder', [
            'categories' => PricingItem::CATEGORIES,
            'initialQuotationId' => $request->query('quotation_id'),
        ]);
    }
}
