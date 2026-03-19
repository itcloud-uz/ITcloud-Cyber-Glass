<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckFaceId;
use App\Http\Middleware\CheckTailscaleIP;

// Dynamic Telegram Webhook
Route::post('/api/webhook/telegram/{token}', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

// Protect admin routes with Tailscale IP check
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
        Route::post('/api/tenants/{id}/upload', function(\Illuminate\Http\Request $request, $id) {
            $tenant = \App\Models\Tenant::findOrFail($id);
            $type = $request->input('type'); // contract or files
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('tenant_docs', 'public');
                if ($type === 'contract') {
                    $tenant->contract_path = $path;
                } else {
                    $files = $tenant->files ?? [];
                    $files[] = $path;
                    $tenant->files = $files;
                }
                $tenant->save();
            }
            return response()->json(['status' => 'success']);
        });

        // Templates API
        Route::post('/api/templates', [\App\Http\Controllers\Api\TemplateController::class, 'store']);
        Route::put('/api/templates/{id}', [\App\Http\Controllers\Api\TemplateController::class, 'update']);
        Route::delete('/api/templates/{id}', [\App\Http\Controllers\Api\TemplateController::class, 'destroy']);

        // bots API
        Route::post('/api/bots', [\App\Http\Controllers\Api\TelegramBotController::class, 'store']);
        Route::put('/api/bots/{id}', [\App\Http\Controllers\Api\TelegramBotController::class, 'update']);
        Route::delete('/api/bots/{id}', [\App\Http\Controllers\Api\TelegramBotController::class, 'destroy']);
        Route::post('/api/bots/{id}/set-webhook', function($id) {
            $bot = \App\Models\TelegramBot::findOrFail($id);
            $domain = request()->getHost();
            $webhookUrl = "https://{$domain}/api/webhook/telegram/{$bot->token}";
            $url = "https://api.telegram.org/bot{$bot->token}/setWebhook?url=" . urlencode($webhookUrl);
            $res = \Illuminate\Support\Facades\Http::get($url);
            return response()->json($res->json());
        });

        // Leads API
        Route::patch('/api/leads/{id}/status', function(\Illuminate\Http\Request $request, $id) {
            \App\Models\Lead::where('id', $id)->update(['status' => $request->status]);
            return response()->json(['status' => 'success']);
        });

        // Employees API
        Route::post('/api/employees', [\App\Http\Controllers\Api\EmployeeController::class, 'store']);
    });

});
