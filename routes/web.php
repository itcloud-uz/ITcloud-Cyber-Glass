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

        // Tenants API (CRUD)
        Route::post('/api/tenants', [\App\Http\Controllers\Api\TenantController::class, 'store']);
        Route::put('/api/tenants/{id}', [\App\Http\Controllers\Api\TenantController::class, 'update']);
        Route::patch('/api/tenants/{id}/status', [\App\Http\Controllers\Api\TenantController::class, 'changeStatus']);
        Route::post('/api/tenants/{id}/subscription', [\App\Http\Controllers\Api\TenantController::class, 'addSubscription']);

        // Templates API
        Route::post('/api/templates', [\App\Http\Controllers\Api\TemplateController::class, 'store']);
        Route::delete('/api/templates/{id}', [\App\Http\Controllers\Api\TemplateController::class, 'destroy']);

        // bots API
        Route::post('/api/bots', [\App\Http\Controllers\Api\TelegramBotController::class, 'store']);
        Route::put('/api/bots/{id}', [\App\Http\Controllers\Api\TelegramBotController::class, 'update']);
        Route::delete('/api/bots/{id}', [\App\Http\Controllers\Api\TelegramBotController::class, 'destroy']);

        // Employees API
        Route::post('/api/employees', [\App\Http\Controllers\Api\EmployeeController::class, 'store']);
    });

});
