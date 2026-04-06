<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Request list working']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Create request working']);
    }
}