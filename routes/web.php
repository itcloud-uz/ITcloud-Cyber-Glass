<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientPortalController;

/*
|--------------------------------------------------------------------------
| ITcloud Core Web Routing System
|--------------------------------------------------------------------------
*/

// [1] COMMON ROUTES (LANDING & TOOLS)
Route::get('/', [LandingController::class, 'index'])->name('home');

Route::controller(LandingController::class)->group(function() {
    Route::get('/constructor', 'constructor')->name('constructor');
    Route::get('/academy', 'academy')->name('academy.landing');
    Route::post('/api/inquiry/submit', 'submitInquiry');
});

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['uz', 'tr', 'ru', 'en'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');



// [2] MASTER ADMIN: AUTHENTICATION (FaceID & OTP)
Route::controller(AuthController::class)->group(function() {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login');
    Route::post('/login/face-id', 'verifyFaceId');
    Route::post('/login/otp/send', 'sendTelegramOtp');
    Route::post('/login/otp/verify', 'verifyOtp');
    Route::post('/logout', 'logout')->name('logout');
});


// [3] MASTER ADMIN: DASHBOARD & MANAGEMENT
Route::middleware(['auth'])->group(function() {
    Route::get('/master', [AdminController::class, 'index'])->name('master.dashboard');
    Route::get('/academy/dashboard', [AdminController::class, 'index'])->name('academy.dashboard');
    
    // Internal API for Dashboard
    Route::get('/internal-api/academy/student/dashboard', [AcademyController::class, 'getStudentDashboard']);
    Route::post('/internal-api/academy/student/mentor/chat', [AcademyController::class, 'mentorChat']);
    
    // Projects & Chat
    Route::get('/internal-api/academy/student/projects', [AcademyController::class, 'getStudentProjects']);
    Route::post('/internal-api/academy/student/projects', [AcademyController::class, 'storeStudentProject']);
    Route::delete('/internal-api/academy/student/projects/{id}', [AcademyController::class, 'deleteStudentProject']);
    
    Route::get('/internal-api/academy/chat', [AcademyController::class, 'getGlobalChat']);
    Route::post('/internal-api/academy/chat', [AcademyController::class, 'sendChatMessage']);
    Route::get('/internal-api/academy/chat/contacts', [AcademyController::class, 'getAcademyContacts']);
});




Route::get('/academy/login', function() {
    return view('academy.login');
})->name('academy.login');




// [4] CLIENT PORTAL: AUTHENTICATION
Route::prefix('client')->name('client.')->group(function () {
    
    Route::controller(ClientAuthController::class)->group(function() {
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login');
        Route::get('/register', 'showRegister')->name('register');
        Route::post('/register', 'register');
        Route::get('/verify/telegram', 'showVerifyTelegram')->name('verify.telegram');
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/password/reset', 'forgotPassword')->name('password.request');
    });

    // [5] CLIENT PORTAL: DASHBOARD & PROJECTS
    Route::middleware(['auth'])->group(function () {
        Route::controller(ClientPortalController::class)->group(function() {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/security', 'security')->name('security');
            Route::get('/sso/{tenant}', 'ssoLogin')->name('sso');
        });
    });

});
