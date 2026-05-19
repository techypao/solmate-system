<?php

use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\ContactMessagePageController;
use App\Http\Controllers\Admin\NewsArticlePageController;
use App\Http\Controllers\Admin\PromotionPageController;
use App\Http\Controllers\Admin\QuotationSettingsPageController;
use App\Http\Controllers\Admin\PricingCatalogPageController;
use App\Http\Controllers\Admin\NotificationPageController;
use App\Http\Controllers\Admin\ProfilePageController;
use App\Http\Controllers\Admin\ReportsPageController;
use App\Http\Controllers\Admin\RequestAssignmentPageController;
use App\Http\Controllers\Admin\TestimonyModerationPageController;
use App\Http\Controllers\Admin\TechnicianRegistrationController;
use App\Http\Controllers\Admin\VisualHighlightPageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerTestimonyPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTestimonyPageController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationItemBuilderPageController;
use App\Models\NewsArticle;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/landing', function () {
    return view('welcome', [
        'newsArticles' => NewsArticle::query()->active()->latest()->get(),
        'promotions' => Promotion::query()->currentlyLive()->orderByDesc('created_at')->get(),
    ]);
})->name('landing');

Route::get('/', function () {
    $headers = [
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma'        => 'no-cache',
    ];

    if (!Auth::check()) {
        return response(view('welcome', [
            'newsArticles' => NewsArticle::query()->active()->latest()->get(),
            'promotions' => Promotion::query()->currentlyLive()->orderByDesc('created_at')->get(),
        ]), 200, $headers);
    }

    if (Auth::user()->role === User::ROLE_ADMIN) {
        return redirect()->route('admin.quotation-settings');
    }

    return response(view('customer.home'), 200, $headers);
})->name('home');

Route::get('/testimonies', [PublicTestimonyPageController::class, 'show'])
    ->name('public.testimonies');

Route::get('/contact', function () {
    return view('public.contact');
})->name('public.contact');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/quotations/item-builder', [QuotationItemBuilderPageController::class, 'show'])
        ->name('quotations.item-builder');

    Route::get('/admin/quotation-settings', [QuotationSettingsPageController::class, 'show'])
        ->name('admin.quotation-settings');

    Route::get('/admin/pricing-catalog', [PricingCatalogPageController::class, 'show'])
        ->name('admin.pricing-catalog');

    Route::get('/admin/technicians/create', [TechnicianRegistrationController::class, 'create'])
        ->name('admin.technicians.create');

    Route::post('/admin/technicians', [TechnicianRegistrationController::class, 'store'])
        ->name('admin.technicians.store');

    Route::get('/admin/technicians/{technician}/edit', [TechnicianRegistrationController::class, 'edit'])
        ->name('admin.technicians.edit');

    Route::put('/admin/technicians/{technician}', [TechnicianRegistrationController::class, 'update'])
        ->name('admin.technicians.update');

    Route::delete('/admin/technicians/{technician}', [TechnicianRegistrationController::class, 'destroy'])
        ->name('admin.technicians.destroy');

    Route::get('/admin/request-assignments', [RequestAssignmentPageController::class, 'show'])
        ->name('admin.request-assignments');

    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/request-assignments/service-popup', [RequestAssignmentPageController::class, 'flashServicePopup'])
            ->name('admin.request-assignments.service-popup');

        Route::get('/admin/customers', [AdminCustomerController::class, 'index'])
            ->name('admin.customers');

        Route::get('/admin/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])
            ->name('admin.customers.edit');

        Route::put('/admin/customers/{customer}', [AdminCustomerController::class, 'update'])
            ->name('admin.customers.update');

        Route::patch('/admin/customers/{customer}/archive', [AdminCustomerController::class, 'archive'])
            ->name('admin.customers.archive');

        Route::delete('/admin/customers/{customer}', [AdminCustomerController::class, 'destroy'])
            ->name('admin.customers.destroy');

        Route::get('/admin/testimonies', [TestimonyModerationPageController::class, 'show'])
            ->name('admin.testimonies');

        Route::get('/admin/notifications', [NotificationPageController::class, 'show'])
            ->name('admin.notifications');

        Route::get('/admin/reports', [ReportsPageController::class, 'show'])
            ->name('admin.reports');

        Route::get('/admin/visual-highlights', [VisualHighlightPageController::class, 'show'])
            ->name('admin.visual-highlights');

        Route::get('/admin/contact-messages', [ContactMessagePageController::class, 'show'])
            ->name('admin.contact-messages');

        Route::get('/admin/news-articles', [NewsArticlePageController::class, 'show'])
            ->name('admin.news-articles');

        Route::get('/admin/promotions', [PromotionPageController::class, 'show'])
            ->name('admin.promotions');

        Route::get('/admin/profile', [ProfilePageController::class, 'show'])
            ->name('admin.profile.show');

        Route::put('/admin/profile', [ProfilePageController::class, 'updateProfile'])
            ->name('admin.profile.update');

        Route::put('/admin/profile/password', [ProfilePageController::class, 'updatePassword'])
            ->name('admin.profile.password.update');
    });

    Route::middleware('role:customer')->group(function () {
        Route::get('/customer/testimonies', [CustomerTestimonyPageController::class, 'show'])
            ->name('customer.testimonies');

        Route::get('/customer/quotation', function () {
            return view('customer.quotation-hub');
        })->name('customer.quotation');

        Route::get('/customer/quotation/create', function () {
            return view('customer.quotation');
        })->name('customer.quotation.create');

        Route::get('/customer/quotation/my-quotations', function () {
            return view('customer.quotations');
        })->name('customer.quotation.index');

        Route::get('/customer/inspection', function () {
            return view('customer.inspection');
        })->name('customer.inspection');

        Route::get('/customer/tracking', function () {
            return view('customer.tracking');
        })->name('customer.tracking');

        Route::get('/customer/final-quotation/{inspection_request_id}', function ($inspection_request_id) {
            return view('customer.final-quotation');
        })->name('customer.final-quotation');

        Route::get('/customer/final-quotation/{inspection_request_id}/download-pdf', [QuotationController::class, 'downloadCustomerFinalQuotationPdf'])
            ->name('customer.final-quotation.download');

        Route::get('/customer/installation', function () {
            return view('customer.installation');
        })->name('customer.installation');

        Route::get('/customer/maintenance', function () {
            return view('customer.maintenance');
        })->name('customer.maintenance');

        Route::get('/customer/mobile-app', function () {
            return view('customer.mobile-app');
        })->name('customer.mobile-app');
    });
});
