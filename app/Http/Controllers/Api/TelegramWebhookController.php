<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClientSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramWebhookController extends Controller
{
    protected $botToken = '8304799073:AAGOi1nbw29OkKY_YhrP3kOJnRGRVq-qVPY';

    public function handle(Request $request)
    {
        $update = $request->all();

        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];

            // /start hook (e.g. /start {hash})
            if (isset($message['text']) && (str_starts_with($message['text'], '/start ') || $message['text'] === '/start')) {
                $hash = str_replace('/start ', '', $message['text']);
                // Agar hash bo'lsa (yangi foydalanuvchi)
                if($hash && $hash !== '/start') {
                    $user = User::where('verification_hash', $hash)->first();
                    if ($user) {
                        $user->update(['telegram_chat_id' => (string)$chatId]);
                        $this->sendContactRequest($chatId, "Salom, {$user->name}! Iltimos, raqamingizni yuboring va tasdiqlang:");
                        return response()->json(['status' => 'ok']);
                    }
                }
            }

            // Contact share hook
            if (isset($message['contact'])) {
                $phone = $message['contact']['phone_number'];
                $telegramChatId = $message['chat']['id'];
                
                // Oxirgi hash egasini qidirmaymiz (agar polling va bot uzoq kelsa), hashni sessionday yoki biror bazadaki bog'liqlikda (chat_id) ko'rish kerak
                // Lekin bu yerda `/start hash` orqali telegram_chat_id ni bog'lab keta olamiz deb xisoblaymiz.
                // Keling, yaxshiroq: /start hash da chat_id ni userga yozib ketamiz.
                
                $user = User::where('telegram_chat_id', (string)$chatId)->first();
                if(!$user) {
                     // Agar /start hash orqali yozilmagan bo'lsa (masalan biror xato yoki foydalanuvchi to'g'ridan to'g'ri yozsa), 
                     // bizga hash kerak edi.
                     // Lekin ko'p holda biz /start hash orqali chat_id ni bog'lab turamiz.
                }

                if ($user) {
                    $user->update([
                        'phone' => $phone,
                        'is_verified' => true,
                        // 'verification_hash' => null, // O'chirib tashlash (user buyrog'i bo'yicha)
                    ]);

                    ClientSecurityLog::create([
                        'user_id' => $user->id,
                        'action' => 'Telegram orqali raqam tasdiqlandi: ' . $phone,
                        'ip_address' => 'Telegram Bot',
                        'user_agent' => 'TelegramWebhook',
                    ]);

                    $this->sendMessage($chatId, "✅ Tasdiqlandi! Saytga qaytishingiz mumkin.");
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function sendContactRequest($chatId, $text)
    {
        $keyboard = [
            'keyboard' => [
                [
                    ['text' => "📞 Raqamni yuborish va Tasdiqlash", 'request_contact' => true]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];

        return Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    protected function sendMessage($chatId, $text)
    {
        return Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}
