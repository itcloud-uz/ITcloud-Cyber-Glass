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

    public function handle(Request $request)
    {
        $message = $request->input('message.text', '');
        $chatId = $request->input('message.chat.id', '');
        
        // Oddiy mantiq: botni tanib olish uchun "agentType"
        // Aslida turli xil bot tokenlari yoki state orqali qaysi agent o'qiyotgani aniqlanadi.
        // Hozir mock uchun biz birinchi so'zni agent tipi sifatida ishlatamiz!
        // Masalan: "sales Sotib olaman", yoki webhook request payloadidan olamiz.
        
        $type = $request->input('agent_type', 'sales'); // Default sales agent

        $response = $this->aiAgent->handleIncomingMessage($type, $message, $chatId);

        return response()->json([
            'status' => 'success',
            'agent_reply' => $response
        ]);
    }
}
