<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiAgentService;
use App\Models\TelegramBot;

class AiChatController extends Controller
{
    private GeminiAgentService $gemini;

    public function __construct(GeminiAgentService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'bot_id' => 'required',
            'message' => 'required|string',
            'chat_id' => 'nullable|string'
        ]);

        $bot = TelegramBot::findOrFail($request->bot_id);
        $chatId = $request->chat_id ?? 'admin_chat_' . auth()->id();
        
        $response = $this->gemini->handleIncomingMessage(
            $bot->agent_type, 
            $request->message, 
            $chatId, 
            $bot->id
        );

        return response()->json([
            'status' => 'success',
            'reply' => $response
        ]);
    }

    public function assignTask(Request $request, $id)
    {
        $request->validate(['task' => 'required|string']);
        
        $bot = TelegramBot::findOrFail($id);
        $bot->current_task = $request->task;
        $bot->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Vazifa agentga muvaffaqiyatli yuklandi.'
        ]);
    }
}
