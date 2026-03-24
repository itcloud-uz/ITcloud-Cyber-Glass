<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClientSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TelegramWebhookController extends Controller
{
    protected $verificationBotToken = '8304799073:AAGOi1nbw29OkKY_YhrP3kOJnRGRVq-qVPY';
    protected $academyBotToken = '8295962421:AAF3uH3did42i14YPZPMYqkrKDfHy8VlTKE';

    public function handle(Request $request, $botType = 'verification')
    {
        $update = $request->all();
        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $token = ($botType === 'academy') ? $this->academyBotToken : $this->verificationBotToken;

        // Academy Logic (I-Ticher bot)
        if ($botType === 'academy') {
            $this->handleAcademyBot($chatId, $message);
            return response()->json(['status' => 'ok']);
        }

        // Standard Verification logic
        if (isset($message['text']) && (str_starts_with($message['text'], '/start ') || $message['text'] === '/start')) {
            $hash = str_replace('/start ', '', $message['text']);
            if($hash && $hash !== '/start') {
                $user = User::where('verification_hash', $hash)->first();
                if ($user) {
                    $user->update(['telegram_chat_id' => (string)$chatId]);
                    $this->sendContactRequest($token, $chatId, "Salom, {$user->name}! Iltimos, raqamingizni yuboring va tasdiqlang:");
                    return response()->json(['status' => 'ok']);
                }
            }
        }

        if (isset($message['contact'])) {
            $phone = $message['contact']['phone_number'];
            $user = User::where('telegram_chat_id', (string)$chatId)->first();
            if ($user) {
                $user->update([
                    'phone' => $phone,
                    'is_verified' => true,
                ]);

                ClientSecurityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Telegram orqali raqam tasdiqlandi: ' . $phone,
                    'ip_address' => 'Telegram Bot',
                    'user_agent' => 'TelegramWebhook',
                ]);

                $this->sendMessage($token, $chatId, "✅ Tasdiqlandi! Saytga qaytishingiz mumkin.");
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function sendContactRequest($token, $chatId, $text)
    {
        $keyboard = [
            'keyboard' => [
                [['text' => "📞 Raqamni yuborish va Tasdiqlash", 'request_contact' => true]]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];

        return Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function handleAcademyBot($chatId, $message)
    {
        $text = $message['text'] ?? '';
        if (str_starts_with($text, '/start')) {
            $token = trim(str_replace('/start', '', $text));
            $app = DB::table('academy_applications')->where('access_token', $token)->first();
            
            if ($app) {
                $assessment = json_decode($app->ai_assessment);
                $logicTest = $assessment->logic_test ?? "I-Ticher dars tayyorlamoqda...";
                $this->sendMessage($this->academyBotToken, $chatId, "Salom {$app->name}! ITcloud Academy'ga xush kelibsiz. Men I-Ticher o'qituvchingizman. 🎓\n\nSizni tahlil qildim. Mana birinchi mantiqiy topshiringiz:\n\n" . $logicTest);
            } else {
                $this->sendMessage($this->academyBotToken, $chatId, "Token xato. Iltimos ariza topshirganingizdan so'ng berilgan link orqali kiring.");
            }
        }
    }

    protected function sendMessage($token, $chatId, $text)
    {
        return Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}
