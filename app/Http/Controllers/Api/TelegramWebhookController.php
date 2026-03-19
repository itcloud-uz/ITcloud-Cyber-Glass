<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiAgentService;

class TelegramWebhookController extends Controller
{
    private GeminiAgentService $aiAgent;

    public function __construct(GeminiAgentService $aiAgent)
    {
        $this->aiAgent = $aiAgent;
    }

    public function handle(Request $request, $token)
    {
        $bot = \App\Models\TelegramBot::where('token', $token)->first();
        if (!$bot) return response()->json(['status' => 'error', 'message' => 'Bot not found']);

        $message = $request->input('message.text', '');
        $chatId = $request->input('message.chat.id', '');
        
        if (empty($message)) return response()->json(['status' => 'ignored']);

        // AI Agentdan javob olish
        $response = $this->aiAgent->handleIncomingMessage($bot->agent_type, $message, $chatId);

        // Telegramga javob qaytarish
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        \Illuminate\Support\Facades\Http::post($url, [
            'chat_id' => $chatId,
            'text' => $response,
            'parse_mode' => 'Markdown'
        ]);

        return response()->json([
            'status' => 'success',
            'sent' => true
        ]);
    }
}
