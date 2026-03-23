<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckFaceId;
use App\Http\Middleware\CheckTailscaleIP;

// Dynamic Telegram Webhook
Route::post('/api/webhook/telegram/{token}', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

// Public Storefront
Route::get('/storefront', [\App\Http\Controllers\StorefrontController::class, 'index'])->name('storefront');

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
        Route::get('/api/dashboard/analytics', [\App\Http\Controllers\Api\AnalyticsController::class, 'dashboardData']);

        // Tenants API (CRUD)
        Route::post('/api/tenants', [\App\Http\Controllers\Api\TenantController::class, 'store']);
        Route::put('/api/tenants/{id}', [\App\Http\Controllers\Api\TenantController::class, 'update']);
        Route::delete('/api/tenants/{id}', [\App\Http\Controllers\Api\TenantController::class, 'destroy']);
        Route::patch('/api/tenants/{id}/status', [\App\Http\Controllers\Api\TenantController::class, 'changeStatus']);
        Route::post('/api/tenants/{id}/subscription', [\App\Http\Controllers\Api\TenantController::class, 'addSubscription']);
        Route::get('/api/subscriptions/{id}/invoice', [\App\Http\Controllers\Api\InvoiceController::class, 'download']);
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

        Route::post('/api/ai/chat', [\App\Http\Controllers\Api\AiChatController::class, 'chat']);
        Route::post('/api/bots/{id}/task', [\App\Http\Controllers\Api\AiChatController::class, 'assignTask']);
        Route::get('/api/bots/{id}/knowledge', function($id) {
            return \App\Models\KnowledgeBase::where('bot_id', $id)->get();
        });
        Route::post('/api/bots/{id}/knowledge', function(\Illuminate\Http\Request $request, $id) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('knowledge', 'public');
                \App\Models\KnowledgeBase::create([
                    'bot_id' => $id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_path' => $path,
                    'content' => 'Tahlil qilindi: ' . $file->getClientOriginalName() // Mock content extraction
                ]);
            }
            return response()->json(['status' => 'success']);
        });
        Route::get('/api/ai/active-chats', function() {
            return \App\Models\AiLog::select('chat_id', 'agent_type', DB::raw('max(created_at) as last_time'))
                ->where('action', 'Chat Message')
                ->groupBy('chat_id', 'agent_type')
                ->orderBy('last_time', 'desc')
                ->take(10)
                ->get();
        });
        Route::get('/api/ai/conversation/{chat_id}', function($chat_id) {
            return \App\Models\AiLog::where('chat_id', $chat_id)
                ->where('action', 'Chat Message')
                ->orderBy('created_at', 'asc')
                ->get();
        });

        // Leads API
        Route::patch('/api/leads/{id}/status', function(\Illuminate\Http\Request $request, $id) {
            \App\Models\Lead::findOrFail($id)->update(['status' => $request->status]);
            return response()->json(['status' => 'success']);
        });

        // Meta Webhooks (Universal)
        Route::get('/webhook/meta/{id}', [\App\Http\Controllers\Api\WebhookController::class, 'verifyMeta']);
        Route::post('/webhook/meta/{id}', [\App\Http\Controllers\Api\WebhookController::class, 'handleMeta']);

        // Payment Webhooks (Secured with Middleware)
        Route::post('/webhook/payme', [\App\Http\Controllers\Api\PaymentController::class, 'handlePaymeWebhook'])
            ->middleware('webhook_source:payme');

        Route::post('/webhook/click', [\App\Http\Controllers\Api\PaymentController::class, 'handlePaymeWebhook']) // Reuse logic for mock
            ->middleware('webhook_source:click');

        // AI Projects (Antigravity Pipeline)
        Route::get('/api/ai-projects', [\App\Http\Controllers\Api\AiProjectController::class, 'index']);
        Route::post('/api/ai-projects', [\App\Http\Controllers\Api\AiProjectController::class, 'store']);

        // Employees API
        Route::post('/api/employees', [\App\Http\Controllers\Api\EmployeeController::class, 'store']);
    });

});
