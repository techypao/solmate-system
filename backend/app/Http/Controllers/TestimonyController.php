<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestimonyController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of testimonies']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Testimony created']);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show testimony', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Testimony updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Testimony deleted', 'id' => $id]);
    }
}