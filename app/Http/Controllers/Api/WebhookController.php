<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TelegramBot;
use App\Services\GeminiAgentService;
use App\Services\MultiChannelService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private $gemini;

    public function __construct(GeminiAgentService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Meta (WhatsApp/Instagram) Verify
     */
    public function verifyMeta(Request $request, $id)
    {
        $bot = TelegramBot::findOrFail($id);
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        if ($mode === 'subscribe' && $token === $bot->meta_verify_token) {
            return response($challenge, 200);
        }
        return response('Forbidden', 403);
    }

    /**
     * Handle Meta (WhatsApp/Instagram) Webhook
     */
    public function handleMeta(Request $request, $id)
    {
        $bot = TelegramBot::findOrFail($id);
        $payload = $request->all();
        
        Log::info("Meta Webhook Received [{$bot->channel_type}]: " . json_encode($payload));

        if ($bot->channel_type === 'whatsapp') {
            $this->processWhatsApp($bot, $payload);
        } else if ($bot->channel_type === 'instagram') {
            $this->processInstagram($bot, $payload);
        }

        return response('OK', 200);
    }

    private function processWhatsApp($bot, $payload)
    {
        // Extract message from WhatsApp Cloud API payload structure
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return;

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) return;

        $value = $changes['value'] ?? null;
        if (!$value) return;

        $message = $value['messages'][0] ?? null;
        if (!$message) return;

        $from = $message['from']; // 998901234567
        $text = $message['text']['body'] ?? '';

        if (!empty($text)) {
            $aiResponse = $this->gemini->handleIncomingMessage($text, $from, $bot->id);
            MultiChannelService::sendWhatsApp($bot->phone_number_id, $from, $aiResponse, $bot->token);
        }
    }

    private function processInstagram($bot, $payload)
    {
        // Extract message from Instagram Graph API payload structure
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return;

        $messaging = $entry['messaging'][0] ?? null;
        if (!$messaging) return;

        $senderId = $messaging['sender']['id'] ?? null;
        $text = $messaging['message']['text'] ?? '';

        if ($senderId && !empty($text)) {
            $aiResponse = $this->gemini->handleIncomingMessage($text, $senderId, $bot->id);
            MultiChannelService::sendInstagram($bot->instagram_account_id, $senderId, $aiResponse, $bot->token);
        }
    }
}
