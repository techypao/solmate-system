<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of technicians']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Technician created']);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show technician', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Technician updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Technician deleted', 'id' => $id]);
    }
}