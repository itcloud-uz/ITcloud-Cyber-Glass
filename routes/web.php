<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckFaceId;
use App\Http\Middleware\CheckTailscaleIP;

// Protect all routes with Tailscale IP check
Route::middleware([CheckTailscaleIP::class])->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // throttle:5,1 meaning max 5 login attempts per minute (Fail2Ban logic)
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/login/face-id', [AuthController::class, 'verifyFaceId']);
    Route::post('/login/otp/send', [AuthController::class, 'sendTelegramOtp']);
    Route::post('/login/otp/verify', [AuthController::class, 'verifyOtp']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard requires standard Auth AND Face ID
    Route::middleware(['auth', CheckFaceId::class])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('home');
    });

});
