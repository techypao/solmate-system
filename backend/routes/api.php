<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TestimonyController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/quotations', [QuotationController::class, 'index']);
Route::post('/quotations', [QuotationController::class, 'store']);

Route::get('/requests', [ServiceRequestController::class, 'index']);
Route::post('/requests', [ServiceRequestController::class, 'store']);

Route::get('/testimonies', [TestimonyController::class, 'index']);
Route::post('/testimonies', [TestimonyController::class, 'store']);