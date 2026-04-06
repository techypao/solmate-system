<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Quotation list working']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Create quotation working']);
    }
}