<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        $image = $request->input('image');

        try {
            $apiKey = env('FACEID_API_KEY', 'itcloud_secret_faceid_2026');
            $originalPath = storage_path('app/public/' . $user->face_id_photo_path);
            $originalImageBase64 = '';
            if (file_exists($originalPath)) {
                $originalImageBase64 = base64_encode(file_get_contents($originalPath));
            }

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json'
            ])->post('http://127.0.0.1:8001/api/v1/verify-face', [
                'live_image' => $image,
                'original_image' => $originalImageBase64,
                'user_id' => $user->id
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    $request->session()->put('face_id_verified', true);
                    return response()->json(['status' => 'success', 'redirect' => '/master']);
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
        if (Auth::check()) {
            $user = Auth::user();
            $otp = rand(100000, 999999);
            $request->session()->put('telegram_otp', $otp);

            $token = env('TELEGRAM_BOT_TOKEN_MASTER');
            // Prioritize user's private chat ID if available, otherwise fallback to master group
            $chatId = !empty($user->telegram_chat_id) ? $user->telegram_chat_id : env('TELEGRAM_MASTER_CHAT_ID');

            $message = "🔐 ITcloud Master Autentifikatsiya: \n\nSizning login kodingiz: $otp\n\nUshbu kodni hech kimga bermang!";
            
            try {
                $response = Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]);

                if ($response->successful()) {
                    Log::info("TELEGRAM OTP: $otp sent to ChatID: $chatId - Success");
                } else {
                    Log::error("TELEGRAM OTP: $otp send FAILED to ChatID: $chatId - Error: " . $response->body());
                    return response()->json(['status' => 'error', 'message' => 'Telegramga kod yuborishda xatolik yuz berdi.'], 500);
                }
            } catch (\Exception $e) {
                Log::error("TELEGRAM OTP ERROR: " . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Telegram serveri bilan bog\'lanishda xatolik.'], 500);
            }

            return response()->json(['status' => 'success', 'message' => 'Telegramga 6 xonali kod yuborildi.']);
        }
        return response()->json(['status' => 'error'], 401);
    }

    public function verifyOtp(Request $request)
    {
        if (Auth::check() && $request->input('otp') == $request->session()->get('telegram_otp')) {
            $request->session()->put('face_id_verified', true);
            return response()->json(['status' => 'success', 'redirect' => '/master']);
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
