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

// APRIL 10 API STRUCTURE
Route::get('/quotations', [QuotationController::class, 'index']);
Route::post('/quotations', [QuotationController::class, 'store']);
Route::get('/quotations/{id}', [QuotationController::class, 'show']);
Route::put('/quotations/{id}', [QuotationController::class, 'update']);
Route::delete('/quotations/{id}', [QuotationController::class, 'destroy']);

Route::get('/inspection-requests', [InspectionRequestController::class, 'index']);
Route::post('/inspection-requests', [InspectionRequestController::class, 'store']);
Route::get('/inspection-requests/{id}', [InspectionRequestController::class, 'show']);
Route::put('/inspection-requests/{id}', [InspectionRequestController::class, 'update']);
Route::delete('/inspection-requests/{id}', [InspectionRequestController::class, 'destroy']);

Route::get('/service-requests', [ServiceRequestController::class, 'index']);
Route::post('/service-requests', [ServiceRequestController::class, 'store']);
Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
Route::put('/service-requests/{id}', [ServiceRequestController::class, 'update']);
Route::delete('/service-requests/{id}', [ServiceRequestController::class, 'destroy']);

Route::get('/technicians', [TechnicianController::class, 'index']);
Route::post('/technicians', [TechnicianController::class, 'store']);
Route::get('/technicians/{id}', [TechnicianController::class, 'show']);
Route::put('/technicians/{id}', [TechnicianController::class, 'update']);
Route::delete('/technicians/{id}', [TechnicianController::class, 'destroy']);

Route::get('/testimonies', [TestimonyController::class, 'index']);
Route::post('/testimonies', [TestimonyController::class, 'store']);
Route::get('/testimonies/{id}', [TestimonyController::class, 'show']);
Route::put('/testimonies/{id}', [TestimonyController::class, 'update']);
Route::delete('/testimonies/{id}', [TestimonyController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Welcome Admin']);
});

Route::middleware(['auth:sanctum', 'role:customer'])->get('/customer-only', function () {
    return response()->json(['message' => 'Welcome Customer']);
});

Route::middleware(['auth:sanctum', 'role:technician'])->get('/technician-only', function () {
    return response()->json(['message' => 'Welcome Technician']);
});