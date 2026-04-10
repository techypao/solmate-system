<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InspectionRequestController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\TestimonyController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// PROTECTED GENERAL ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::put('/quotations/{id}', [QuotationController::class, 'update']);

    Route::put('/service-requests/{id}/assign-technician', [ServiceRequestController::class, 'assignTechnician']);
});

// ADMIN ROUTES
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin-only', function () {
        return response()->json(['message' => 'Welcome Admin']);
    });
});

// TECHNICIAN ROUTES
Route::middleware(['auth:sanctum', 'role:technician'])->group(function () {
    Route::get('/technician-only', function () {
        return response()->json(['message' => 'Welcome Technician']);
    });

    Route::get('/technician/service-requests', [ServiceRequestController::class, 'assignedRequests']);
    Route::put('/technician/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']);
});

// CUSTOMER ROUTES
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/customer-only', function () {
        return response()->json(['message' => 'Welcome Customer']);
    });

    Route::get('/inspection-requests', [InspectionRequestController::class, 'index']);
    Route::post('/inspection-requests', [InspectionRequestController::class, 'store']);

    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
});