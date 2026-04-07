<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TestimonyController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/quotations', [QuotationController::class, 'index']);
Route::post('/quotations', [QuotationController::class, 'store']);

Route::get('/requests', [ServiceRequestController::class, 'index']);
Route::post('/requests', [ServiceRequestController::class, 'store']);

Route::get('/testimonies', [TestimonyController::class, 'index']);
Route::post('/testimonies', [TestimonyController::class, 'store']);

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