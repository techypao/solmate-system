<?php

use App\Http\Controllers\Admin\NewsArticleController as AdminNewsArticleController;
use App\Http\Controllers\Admin\PricingItemController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\QuotationSettingsController;
use App\Http\Controllers\Admin\VisualHighlightController as AdminVisualHighlightController;
use App\Http\Controllers\Api\AdminChatConversationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatConversationController;
use App\Http\Controllers\Api\CustomerAccountController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\TechnicianAccountController;
use App\Http\Controllers\CompletionReportController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\InspectionRequestController;
use App\Http\Controllers\NewsArticleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PreferredDateAvailabilityController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationLineItemController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TestimonyController;
use App\Http\Controllers\VisualHighlightController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Public routes located in the homeapage that does NOT require login
//Route::method('/url-path', [ControllerName::class, 'methodName']); 
// -> the structure of routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/public/testimonies', [TestimonyController::class, 'publicIndex']);
Route::get('/public/visual-highlights', [VisualHighlightController::class, 'index']);
Route::get('/public/news-articles', [NewsArticleController::class, 'index']);
Route::get('/public/promotions', [PromotionController::class, 'index']);
Route::post('/contact-messages', [ContactMessageController::class, 'store']);

// PROTECTED GENERAL ROUTES
Route::middleware(['auth:sanctum', 'active.user', 'verified.email'])->group(function () {
    //auth:sanctum -> user must be logged in using laravel sanctum token authentication
    //active.user -> user account must still be active
    //verified. email -> user email must be verified
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::post('/save-fcm-token', [DeviceTokenController::class, 'store']);
    Route::post('/remove-fcm-token', [DeviceTokenController::class, 'destroy']);
    Route::post('/save-device-token', [DeviceTokenController::class, 'store']);

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::get('/pricing-items', [PricingItemController::class, 'catalog']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::put('/quotations/{id}', [QuotationController::class, 'update']);
    Route::match(['put', 'patch'], '/quotations/{quotation}/line-items', [QuotationLineItemController::class, 'replace']);
    Route::get('/preferred-date-availability', PreferredDateAvailabilityController::class);
});

// ADMIN ROUTES
Route::middleware(['auth:sanctum', 'verified.email', 'role:admin'])->group(function () {
    Route::get('/admin-only', function () {
        return response()->json(['message' => 'Welcome Admin']);
    });

    Route::put('/service-requests/{id}/assign-technician', [ServiceRequestController::class, 'assignTechnician']);
    Route::post('/admin/manual-inspection-requests', [ServiceRequestController::class, 'storeManualInspection']);
    Route::match(['put', 'patch'], '/admin/service-requests/{id}/preferred-date', [ServiceRequestController::class, 'updatePreferredDate']);
    Route::put('/admin/service-requests/{id}/status', [ServiceRequestController::class, 'updateAdminStatus']);
    Route::put('/inspection-requests/{id}/assign-technician', [InspectionRequestController::class, 'assignTechnician']);
    Route::match(['put', 'patch'], '/inspection-requests/{id}/preferred-date', [InspectionRequestController::class, 'updatePreferredDate']);
    Route::put('/admin/inspection-requests/{id}/status', [InspectionRequestController::class, 'updateAdminStatus']);
    Route::get('/admin/quotation-settings', [QuotationSettingsController::class, 'show']);
    Route::match(['put', 'patch'], '/admin/quotation-settings', [QuotationSettingsController::class, 'update']);
    Route::get('/admin/pricing-items', [PricingItemController::class, 'index']);
    Route::post('/admin/pricing-items', [PricingItemController::class, 'store']);
    Route::match(['put', 'patch'], '/admin/pricing-items/{pricingItem}', [PricingItemController::class, 'update']);
    Route::delete('/admin/pricing-items/{pricingItem}', [PricingItemController::class, 'destroy']);
    Route::get('/admin/news-articles', [AdminNewsArticleController::class, 'index']);
    Route::post('/admin/news-articles', [AdminNewsArticleController::class, 'store']);
    Route::patch('/admin/news-articles/{newsArticle}/toggle', [AdminNewsArticleController::class, 'toggle']);
    Route::post('/admin/news-articles/{newsArticle}/refresh', [AdminNewsArticleController::class, 'refresh']);
    Route::delete('/admin/news-articles/{newsArticle}', [AdminNewsArticleController::class, 'destroy']);
    Route::get('/admin/promotions', [AdminPromotionController::class, 'index']);
    Route::post('/admin/promotions', [AdminPromotionController::class, 'store']);
    Route::post('/admin/promotions/{promotion}', [AdminPromotionController::class, 'update']);
    Route::patch('/admin/promotions/{promotion}/toggle', [AdminPromotionController::class, 'toggle']);
    Route::delete('/admin/promotions/{promotion}', [AdminPromotionController::class, 'destroy']);
    Route::get('/admin/visual-highlights', [AdminVisualHighlightController::class, 'index']);
    Route::post('/admin/visual-highlights', [AdminVisualHighlightController::class, 'store']);
    Route::match(['put', 'patch'], '/admin/visual-highlights/{visualHighlight}', [AdminVisualHighlightController::class, 'update']);
    Route::delete('/admin/visual-highlights/{visualHighlight}', [AdminVisualHighlightController::class, 'destroy']);
    Route::get('/admin/testimonies', [TestimonyController::class, 'adminIndex']);
    Route::patch('/admin/testimonies/{id}/approve', [TestimonyController::class, 'approve']);
    Route::patch('/admin/testimonies/{id}/reject', [TestimonyController::class, 'reject']);
    Route::put('/admin/testimonies/{id}', [TestimonyController::class, 'adminUpdate']);
    Route::delete('/admin/testimonies/{id}', [TestimonyController::class, 'adminDestroy']);

    // Contact Messages
    Route::get('/admin/contact-messages', [ContactMessageController::class, 'index']);
    Route::patch('/admin/contact-messages/{id}/status', [ContactMessageController::class, 'updateStatus']);
    Route::delete('/admin/contact-messages/{id}', [ContactMessageController::class, 'destroy']);

    Route::get('/admin/chat/conversations', [AdminChatConversationController::class, 'index']);
    Route::get('/admin/chat/conversations/{conversation}', [AdminChatConversationController::class, 'show']);
    Route::post('/admin/chat/conversations/{conversation}/takeover', [AdminChatConversationController::class, 'takeOver']);
    Route::post('/admin/chat/conversations/{conversation}/return-to-bot', [AdminChatConversationController::class, 'returnToBot']);
    Route::post('/admin/chat/conversations/{conversation}/messages', [AdminChatConversationController::class, 'storeMessage']);
});

// TECHNICIAN ROUTES
Route::middleware(['auth:sanctum', 'verified.email', 'role:technician'])->group(function () {
    Route::get('/technician-only', function () {
        return response()->json(['message' => 'Welcome Technician']);
    });

    Route::get('/technician/service-requests', [ServiceRequestController::class, 'assignedRequests']);
    Route::put('/technician/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']);
    Route::post('/technician/service-requests/{id}/completion-request', [ServiceRequestController::class, 'requestCompletion']);
    Route::post('/technician/service-requests/{id}/completion-report', [CompletionReportController::class, 'submitForService']);

    Route::get('/technician/inspection-requests', [InspectionRequestController::class, 'assignedToTechnician']);
    Route::put('/technician/inspection-requests/{id}/status', [InspectionRequestController::class, 'updateStatus']);
    Route::post('/technician/inspection-requests/{id}/completion-report', [CompletionReportController::class, 'submitForInspection']);

    Route::get('/technician/final-quotation-options', [QuotationController::class, 'getFinalQuotationOptions']);
    Route::post('/technician/final-quotations', [QuotationController::class, 'storeFinalQuotation']);
    Route::put('/technician/account', [TechnicianAccountController::class, 'updateProfile']);
    Route::put('/technician/account/password', [TechnicianAccountController::class, 'updatePassword']);
});

// CUSTOMER ROUTES
Route::middleware(['auth:sanctum', 'verified.email', 'role:customer'])->group(function () {
    Route::get('/customer-only', function () {
        return response()->json(['message' => 'Welcome Customer']);
    });

    Route::get('/inspection-requests', [InspectionRequestController::class, 'index']);
    Route::post('/inspection-requests', [InspectionRequestController::class, 'store']);
    Route::put('/inspection-requests/{id}/cancel', [InspectionRequestController::class, 'cancelByCustomer']);

    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
    Route::put('/service-requests/{id}/cancel', [ServiceRequestController::class, 'cancelByCustomer']);
    Route::get('/my-testimonies', [TestimonyController::class, 'myIndex']);
    Route::post('/testimonies', [TestimonyController::class, 'store']);
    Route::put('/testimonies/{id}', [TestimonyController::class, 'update']);
    Route::delete('/testimonies/{id}', [TestimonyController::class, 'destroy']);

    Route::get('/customer/final-quotations/{inspection_request_id}', [QuotationController::class, 'getCustomerFinalQuotation']);
    Route::put('/customer/account', [CustomerAccountController::class, 'updateProfile']);
    Route::put('/customer/account/password', [CustomerAccountController::class, 'updatePassword']);
    Route::get('/chat/conversation', [ChatConversationController::class, 'show']);
    Route::post('/chat/conversation/messages', [ChatConversationController::class, 'storeMessage']);
    Route::post('/chat/conversation/escalate', [ChatConversationController::class, 'escalate']);
});
