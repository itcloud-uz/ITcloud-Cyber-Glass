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

    public function getActiveChats()
    {
        $chats = \App\Models\AiLog::select('chat_id', 'agent_type', \DB::raw('MAX(created_at) as last_time'))
                    ->whereNotNull('chat_id')
                    ->groupBy('chat_id', 'agent_type')
                    ->orderBy('last_time', 'desc')
                    ->get();
        return response()->json($chats);
    }

    public function getConversation($chatId)
    {
        $history = \App\Models\AiLog::where('chat_id', $chatId)
                    ->orderBy('created_at', 'asc')
                    ->get();
        return response()->json($history);
    }
}
