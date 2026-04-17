<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TestimonyModerationPageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.testimonies');
    }
}
