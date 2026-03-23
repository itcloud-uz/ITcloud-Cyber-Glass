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

        // AI Agent ishini Queue ga o'tkazish
        \App\Jobs\ProcessAiChatMessage::dispatch($message, (string)$chatId, $bot->id, $bot->agent_type);

        return response()->json([
            'status' => 'success',
            'queued' => true
        ]);
    }
}
