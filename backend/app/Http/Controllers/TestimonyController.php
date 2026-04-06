<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestimonyController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Testimony list working']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Create testimony working']);
    }
}