<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Require a valid Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Management
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::middleware('role:admin')->group(function () {
        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments', [PaymentController::class, 'store']);
        Route::get('payments/{id}', [PaymentController::class, 'show']);
        Route::put('payments/{id}', [PaymentController::class, 'update']);
        Route::delete('payments/{id}', [PaymentController::class, 'destroy']);
    });
});
