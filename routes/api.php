<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhook/telegram', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);
Route::post('/webhook/payme', [\App\Http\Controllers\Api\PaymentController::class, 'handlePaymeWebhook']);
