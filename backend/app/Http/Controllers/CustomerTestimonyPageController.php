<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CustomerTestimonyPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_CUSTOMER, 403);

        return view('customer.testimonies');
    }
}
