<?php

use App\Http\Controllers\Admin\PricingItemController;
use App\Http\Controllers\Admin\QuotationSettingsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAccountController;
use App\Http\Controllers\Api\TechnicianAccountController;
use App\Http\Controllers\InspectionRequestController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationLineItemController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TestimonyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/public/testimonies', [TestimonyController::class, 'publicIndex']);

// PROTECTED GENERAL ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::get('/pricing-items', [PricingItemController::class, 'catalog']);
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::put('/quotations/{id}', [QuotationController::class, 'update']);
    Route::match(['put', 'patch'], '/quotations/{quotation}/line-items', [QuotationLineItemController::class, 'replace']);
});

// ADMIN ROUTES
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin-only', function () {
        return response()->json(['message' => 'Welcome Admin']);
    });

    Route::put('/service-requests/{id}/assign-technician', [ServiceRequestController::class, 'assignTechnician']);
    Route::match(['put', 'patch'], '/admin/service-requests/{id}/preferred-date', [ServiceRequestController::class, 'updatePreferredDate']);
    Route::put('/admin/service-requests/{id}/status', [ServiceRequestController::class, 'updateAdminStatus']);
    Route::put('/inspection-requests/{id}/assign-technician', [InspectionRequestController::class, 'assignTechnician']);
    Route::match(['put', 'patch'], '/inspection-requests/{id}/preferred-date', [InspectionRequestController::class, 'updatePreferredDate']);
    Route::get('/admin/quotation-settings', [QuotationSettingsController::class, 'show']);
    Route::match(['put', 'patch'], '/admin/quotation-settings', [QuotationSettingsController::class, 'update']);
    Route::get('/admin/pricing-items', [PricingItemController::class, 'index']);
    Route::post('/admin/pricing-items', [PricingItemController::class, 'store']);
    Route::match(['put', 'patch'], '/admin/pricing-items/{pricingItem}', [PricingItemController::class, 'update']);
    Route::get('/admin/testimonies', [TestimonyController::class, 'adminIndex']);
    Route::patch('/admin/testimonies/{id}/approve', [TestimonyController::class, 'approve']);
    Route::patch('/admin/testimonies/{id}/reject', [TestimonyController::class, 'reject']);
    Route::put('/admin/testimonies/{id}', [TestimonyController::class, 'adminUpdate']);
    Route::delete('/admin/testimonies/{id}', [TestimonyController::class, 'adminDestroy']);
});

// TECHNICIAN ROUTES
Route::middleware(['auth:sanctum', 'role:technician'])->group(function () {
    Route::get('/technician-only', function () {
        return response()->json(['message' => 'Welcome Technician']);
    });

    Route::get('/technician/service-requests', [ServiceRequestController::class, 'assignedRequests']);
    Route::put('/technician/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']);
    Route::post('/technician/service-requests/{id}/completion-request', [ServiceRequestController::class, 'requestCompletion']);

    Route::get('/technician/inspection-requests', [InspectionRequestController::class, 'assignedToTechnician']);
    Route::put('/technician/inspection-requests/{id}/status', [InspectionRequestController::class, 'updateStatus']);

    Route::get('/technician/final-quotation-options', [QuotationController::class, 'getFinalQuotationOptions']);
    Route::post('/technician/final-quotations', [QuotationController::class, 'storeFinalQuotation']);
    Route::put('/technician/account', [TechnicianAccountController::class, 'updateProfile']);
    Route::put('/technician/account/password', [TechnicianAccountController::class, 'updatePassword']);
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
    Route::get('/my-testimonies', [TestimonyController::class, 'myIndex']);
    Route::post('/testimonies', [TestimonyController::class, 'store']);
    Route::put('/testimonies/{id}', [TestimonyController::class, 'update']);
    Route::delete('/testimonies/{id}', [TestimonyController::class, 'destroy']);

    Route::get('/customer/final-quotations/{inspection_request_id}', [QuotationController::class, 'getCustomerFinalQuotation']);
    Route::put('/customer/account', [CustomerAccountController::class, 'updateProfile']);
    Route::put('/customer/account/password', [CustomerAccountController::class, 'updatePassword']);
});
