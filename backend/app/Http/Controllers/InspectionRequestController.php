<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InspectionRequestController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of inspection requests']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Inspection request created']);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show inspection request', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Inspection request updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Inspection request deleted', 'id' => $id]);
    }
}