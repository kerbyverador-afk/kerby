<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantPaymentController;
use App\Http\Controllers\UserController;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Protected Admin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function (WeatherService $weatherService) {
        return view('admin.dashboard', [
            'weather' => $weatherService->current(),
        ]);
    })->name('admin.dashboard');

    Route::post('/payments/reminders/send', [PaymentController::class, 'sendPaymentReminders'])->name('payments.reminders.send');
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::resource('payments', PaymentController::class);
    Route::resource('users', UserController::class);
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
        ->name('auth.google');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

// Central Dashboard Redirection
Route::get('/dashboard', function () {
    $role = Auth::user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('tenant.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Protected Tenant Routes
Route::middleware(['auth', 'role:tenant'])->group(function () {
    Route::get('/tenant/dashboard', [TenantPaymentController::class, 'dashboard'])->name('tenant.dashboard');
    Route::get('/tenant/payments/create', [TenantPaymentController::class, 'create'])->name('tenant.payments.create');
    Route::get('/tenant/payments/history', [TenantPaymentController::class, 'history'])->name('tenant.payments.history');
    Route::post('/tenant/payments', [TenantPaymentController::class, 'store'])->name('tenant.payments.store');
    Route::post('/tenant/notifications/{notification}/read', [TenantPaymentController::class, 'markNotificationAsRead'])->name('tenant.notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/payments/{payment}/receipt.pdf', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/{payment}/uploaded-receipt/view', [PaymentController::class, 'viewUploadedReceipt'])->name('payments.uploaded-receipt.view');
    Route::get('/payments/{payment}/uploaded-receipt', [PaymentController::class, 'uploadedReceipt'])->name('payments.uploaded-receipt');
});

// Profile Routes (Required by Breeze Navigation)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
