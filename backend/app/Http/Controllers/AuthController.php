<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        return response()->json(['message' => 'Register method working']);
    }

    public function login(Request $request)
    {
        return response()->json(['message' => 'Login method working']);
    }
}