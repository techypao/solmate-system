<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of quotations']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Quotation created']);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show quotation', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Quotation updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Quotation deleted', 'id' => $id]);
    }
}