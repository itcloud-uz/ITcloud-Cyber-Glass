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
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Sessiya topilmadi. Qaytadan urinib ko\'ring.'], 401);
        }

        if (!$user->is_face_id_enabled || empty($user->face_id_photo_path)) {
            return response()->json(['status' => 'error', 'message' => 'Sizning Face ID rasmingiz bazaga kiritilmagan! Iltimos, OTP orqali kiring.'], 401);
        }

        // 1. Get image from request (base64)
        $image = $request->input('image');
        
        // 2. Call Python FaceID Server securely
        try {
            $apiKey = env('FACEID_API_KEY', 'itcloud_secret_faceid_2026');
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json'
            ])->post('http://127.0.0.1:8000/api/v1/verify-face', [
                'image' => $image,
                'user_id' => $user->id
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    $request->session()->put('face_id_verified', true);
                    return response()->json(['status' => 'success', 'redirect' => '/']);
                }
            }
            
            return response()->json([
                'status' => 'error', 
                'message' => $response->json()['message'] ?? 'Yuz tanilmadi yoxud liveness o\'tmadi'
            ], 401);

        } catch (\Exception $e) {
            Log::error("FaceID Server Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'FaceID serveri bilan bog\'lanishda xatolik!'], 500);
        }
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
