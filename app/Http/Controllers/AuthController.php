<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // We do NOT redirect to dashboard directly.
            // We tell frontend to switch to Face ID camera mode.
            return response()->json(['status' => 'success', 'step' => 'face_id_required']);
        }

        return response()->json(['status' => 'error', 'message' => 'Noto\'g\'ri login yoki parol'], 401);
    }

    public function verifyFaceId(Request $request)
    {
        // In real world, the python service would verify and send a token or we would call Python API 
        // with the image data. Here we mock:
        $token = $request->input('face_token');
        if ($token === 'face_id_success') { 
            $request->session()->put('face_id_verified', true);
            return response()->json(['status' => 'success', 'redirect' => '/']);
        }

        return response()->json(['status' => 'error', 'message' => 'Yuz tanilmadi yoxud liveness o\'tmadi'], 401);
    }
    
    public function sendTelegramOtp(Request $request)
    {
        // This is only allowed if user is already authenticated via password (Session exists)
        if (Auth::check()) {
            $otp = rand(100000, 999999);
            $request->session()->put('telegram_otp', $otp);
            
            // Mocking Telegram Send Message logic
            Log::info("TELEGRAM OTP: $otp sent to Master Admin.");
            
            return response()->json(['status' => 'success', 'message' => 'Telegramga 6 xonali kod yuborildi.']);
        }
        return response()->json(['status' => 'error'], 401);
    }
    
    public function verifyOtp(Request $request)
    {
        if (Auth::check() && $request->input('otp') == $request->session()->get('telegram_otp')) {
            $request->session()->put('face_id_verified', true);
            return response()->json(['status' => 'success', 'redirect' => '/']);
        }
        return response()->json(['status' => 'error', 'message' => 'Kod xato'], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
