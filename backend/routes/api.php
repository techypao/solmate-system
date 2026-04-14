<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InspectionRequestController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\TestimonyController;
use App\Http\Controllers\Admin\QuotationSettingsController;

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

    Route::put('/inspection-requests/{id}/assign-technician', [InspectionRequestController::class, 'assignTechnician']);
    Route::get('/admin/quotation-settings', [QuotationSettingsController::class, 'show']);
    Route::match(['put', 'patch'], '/admin/quotation-settings', [QuotationSettingsController::class, 'update']);
});

// TECHNICIAN ROUTES
Route::middleware(['auth:sanctum', 'role:technician'])->group(function () {
    Route::get('/technician-only', function () {
        return response()->json(['message' => 'Welcome Technician']);
    });

    Route::get('/technician/service-requests', [ServiceRequestController::class, 'assignedRequests']);
    Route::put('/technician/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']);

    Route::get('/technician/inspection-requests', [InspectionRequestController::class, 'assignedToTechnician']);
    Route::put('/technician/inspection-requests/{id}/status', [InspectionRequestController::class, 'updateStatus']);

    Route::get('/technician/final-quotation-options', [QuotationController::class, 'getFinalQuotationOptions']);
    Route::post('/technician/final-quotations', [QuotationController::class, 'storeFinalQuotation']);
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

    Route::get('/customer/final-quotations/{inspection_request_id}', [QuotationController::class, 'getCustomerFinalQuotation']);
});
