<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of service requests']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Service request created']);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show service request', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Service request updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Service request deleted', 'id' => $id]);
    }
}