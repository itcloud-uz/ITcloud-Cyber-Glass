<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GeminiAgentService;
use App\Services\MultiChannelService;
use App\Models\TelegramBot;
use Illuminate\Support\Facades\Log;

class ProcessAiChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $chatId;
    protected $botId;
    protected $agentType;

    /**
     * Create a new job instance.
     */
    public function __construct($message, $chatId, $botId = null, $agentType = 'support')
    {
        $this->message = $message;
        $this->chatId = $chatId;
        $this->botId = $botId;
        $this->agentType = $agentType;
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiAgentService $gemini): void
    {
        Log::info("Processing AI Chat Message in background for Bot ID: {$this->botId}");

        // 1. Get Gemini Response
        $aiResponse = $gemini->handleIncomingMessage($this->agentType, $this->message, $this->chatId, $this->botId);

        // 2. If it's a channel message, send it back via API
        if ($this->botId) {
            $bot = TelegramBot::find($this->botId);
            if (!$bot) return;

            if ($bot->channel_type === 'telegram') {
                MultiChannelService::sendTelegram($bot->token, $this->chatId, $aiResponse);
            } elseif ($bot->channel_type === 'whatsapp') {
                MultiChannelService::sendWhatsApp($bot->phone_number_id, $this->chatId, $aiResponse, $bot->token);
            } elseif ($bot->channel_type === 'instagram') {
                MultiChannelService::sendInstagram($bot->instagram_account_id, $this->chatId, $aiResponse, $bot->token);
            }
        }
    }
}
