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
use App\Http\Controllers\Api\AcademyController;
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

Route::get('/dashboard/analytics', [AnalyticsController::class, 'dashboardData']);

// Tenants
Route::apiResource('tenants', TenantController::class)->except(['index', 'show']);
Route::patch('/tenants/{id}/status', [TenantController::class, 'changeStatus']);
Route::post('/tenants/{id}/subscription', [TenantController::class, 'addSubscription']);
Route::post('/tenants/{id}/upload', function(Request $request, $id) {
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
Route::post('/auth/verify-master', [EmployeeController::class, 'verifyMaster']);

// Projects
Route::apiResource('ai-projects', AiProjectController::class)->only(['index', 'store']);

// Templates
Route::apiResource('templates', TemplateController::class)->except(['create', 'edit', 'show']);

// Bots
Route::apiResource('bots', TelegramBotController::class)->except(['create', 'edit', 'show']);
Route::post('/bots/{id}/task', [AiChatController::class, 'assignTask']);
Route::post('/bots/{id}/set-webhook', function ($id) { 
    // Logic to be implemented via Bot Service in next phase
    return response()->json(['status' => 'success', 'message' => 'Webhook registration requested.']); 
});
Route::get('/bots/{id}/knowledge', function ($id) { return response()->json([]); });
Route::post('/bots/{id}/knowledge', function ($id) { return response()->json(['status' => 'success']); });

// Chat & Monitor
Route::post('/ai/chat', [AiChatController::class, 'chat']);
Route::get('/ai/active-chats', [AiChatController::class, 'getActiveChats']);
Route::get('/ai/conversation/{id}', [AiChatController::class, 'getConversation']);

Route::prefix('client')->group(function () {
    Route::middleware('auth:sanctum')->get('/verify/status', [TelegramVerificationController::class, 'checkStatus']);
    Route::middleware('web', 'auth')->get('/verify/status', [TelegramVerificationController::class, 'checkStatus']);
    Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
    Route::post('/academy/webhook', function(Request $request) {
        return app(TelegramWebhookController::class)->handle($request, 'academy');
    });
});

// PR auto-bot manual trigger
Route::post('/pr-bot/trigger', function () {
    \Illuminate\Support\Facades\Artisan::call('app:send-daily-project-update', ['--force' => true]);
    return response()->json(['status' => 'success']);
});

// Academy API
Route::get('/academy/stats', [AcademyController::class, 'getStats']);
Route::post('/academy/apply', [AcademyController::class, 'apply']);
Route::get('/academy/applications', [AcademyController::class, 'getApplications']);
Route::put('/academy/applications/{id}', [AcademyController::class, 'updateApplication']);
Route::delete('/academy/applications/{id}', [AcademyController::class, 'deleteApplication']);
Route::post('/academy/applications/{id}/approve', [AcademyController::class, 'approveApplication']);

// Academy Advanced Management
Route::get('/academy/courses', [AcademyController::class, 'getCourses']);
Route::post('/academy/courses', [AcademyController::class, 'storeCourse']);
Route::get('/academy/mentors', [AcademyController::class, 'getMentors']);
Route::post('/academy/mentors', [AcademyController::class, 'storeMentor']);
Route::get('/academy/students', [AcademyController::class, 'getStudents']);
Route::put('/academy/students/{id}/profile', [AcademyController::class, 'updateStudentProfile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/academy/student/dashboard', [AcademyController::class, 'getStudentDashboard']);
    Route::post('/academy/student/mentor/chat', [AcademyController::class, 'mentorChat']);
});



Route::middleware('auth:sanctum')->group(function() {
    Route::get('/academy/dashboard', [AcademyController::class, 'getStudentDashboard']);
    Route::post('/academy/lesson', [AcademyController::class, 'generateLesson']);
    Route::post('/academy/sandbox', [AcademyController::class, 'submitSandbox']);
    Route::post('/academy/task/submit', [AcademyController::class, 'submitTask']);
});
