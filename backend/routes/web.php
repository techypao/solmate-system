<?php

use App\Http\Controllers\Admin\QuotationSettingsPageController;
use App\Http\Controllers\Admin\PricingCatalogPageController;
use App\Http\Controllers\Admin\ProfilePageController;
use App\Http\Controllers\Admin\RequestAssignmentPageController;
use App\Http\Controllers\Admin\TestimonyModerationPageController;
use App\Http\Controllers\Admin\TechnicianRegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerTestimonyPageController;
use App\Http\Controllers\PublicTestimonyPageController;
use App\Http\Controllers\QuotationItemBuilderPageController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->role === User::ROLE_ADMIN) {
        return redirect()->route('admin.quotation-settings');
    }

    return redirect()->route('dashboard');
})->name('home');

Route::get('/testimonies', [PublicTestimonyPageController::class, 'show'])
    ->name('public.testimonies');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        return view('dashboard', [
            'user' => $request->user(),
        ]);
    })->name('dashboard');

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

    Route::get('/admin/request-assignments', [RequestAssignmentPageController::class, 'show'])
        ->name('admin.request-assignments');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/testimonies', [TestimonyModerationPageController::class, 'show'])
            ->name('admin.testimonies');

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
    });
});
