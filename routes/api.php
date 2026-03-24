<?php

use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\TelegramVerificationController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AiProjectController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\TelegramBotController;
use App\Http\Controllers\Api\AiChatController;
use App\Models\PriceService;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Master Panel API Setup (Unlocks Save/Update buttons)
Route::post('/settings', [SettingController::class, 'update']);
Route::put('/price-services/{id}', function (Request $request, $id) {
    try {
        $ps = PriceService::findOrFail($id);
        $ps->update($request->only(['base_price', 'max_price', 'min_days']));
        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
    }
});

Route::get('/dashboard/analytics', [AnalyticsController::class, 'index']);

// Tenants
Route::apiResource('tenants', TenantController::class)->except(['index', 'show']);
Route::patch('/tenants/{id}/status', [TenantController::class, 'changeStatus']);
Route::post('/tenants/{id}/subscription', [TenantController::class, 'addSubscription']);
Route::post('/tenants/{id}/upload', function(Request $request, $id) {
    // Missing upload logic shim
    if ($request->hasFile('file')) {
        $path = $request->file('file')->store('tenants/'.$id, 'public');
        $t = Tenant::findOrFail($id);
        if ($request->type === 'contract') {
            $t->contract_path = $path;
        } else {
            $f = $t->files ?? [];
            $f[] = $path;
            $t->files = $f;
        }
        $t->save();
        return response()->json(['status' => 'success', 'path' => $path]);
    }
    return response()->json(['status' => 'error'], 400);
});

// Leads (Basic toggle)
Route::patch('/leads/{id}/status', function(Request $request, $id) {
    $lead = \App\Models\Lead::findOrFail($id);
    $lead->update(['status' => $request->status]);
    return response()->json(['status' => 'success']);
});

// Employees
Route::apiResource('employees', EmployeeController::class)->except(['index', 'show']);

// Projects
Route::apiResource('ai-projects', AiProjectController::class)->only(['index', 'store']);

// Templates
Route::apiResource('templates', TemplateController::class)->except(['create', 'edit', 'show']);

// Bots
Route::apiResource('bots', TelegramBotController::class)->except(['create', 'edit', 'show']);
Route::post('/bots/{id}/task', [AiChatController::class, 'assignTask']);
Route::post('/bots/{id}/set-webhook', function ($id) { return response()->json(['ok' => true]); });
Route::get('/bots/{id}/knowledge', function ($id) { return response()->json([]); });
Route::post('/bots/{id}/knowledge', function ($id) { return response()->json(['status' => 'success']); });

// Chat & Monitor
Route::post('/ai/chat', [AiChatController::class, 'chat']);
Route::get('/ai/active-chats', function() { return response()->json([]); });
Route::get('/ai/conversation/{id}', function() { return response()->json([]); });

Route::prefix('client')->group(function () {
    Route::middleware('auth:sanctum')->get('/verify/status', [TelegramVerificationController::class, 'checkStatus']);
    Route::middleware('web', 'auth')->get('/verify/status', [TelegramVerificationController::class, 'checkStatus']);
    Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
});

// PR auto-bot manual trigger
Route::post('/pr-bot/trigger', function () {
    \Illuminate\Support\Facades\Artisan::call('app:send-daily-project-update', ['--force' => true]);
    return response()->json(['status' => 'success']);
});
