<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Http\Request;

class PricingCatalogPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.pricing-catalog', [
            'categories' => PricingItem::CATEGORIES,
        ]);
    }
}
