<?php

use App\Http\Controllers\Admin\QuotationSettingsPageController;
use App\Http\Controllers\Admin\RequestAssignmentPageController;
use App\Http\Controllers\AuthController;
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

    Route::get('/admin/quotation-settings', [QuotationSettingsPageController::class, 'show'])
        ->name('admin.quotation-settings');

    Route::get('/admin/request-assignments', [RequestAssignmentPageController::class, 'show'])
        ->name('admin.request-assignments');
});
