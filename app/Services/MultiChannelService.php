<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MultiChannelService
{
    /**
     * Send message back to Meta (WhatsApp)
     */
    public static function sendWhatsApp($phoneNumberId, $to, $message, $token)
    {
        $url = "https://graph.facebook.com/v20.0/{$phoneNumberId}/messages";
        
        return Http::withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message]
        ]);
    }

    /**
     * Send message back to Instagram Direct
     */
    public static function sendInstagram($igAccountId, $to, $message, $token)
    {
        $url = "https://graph.facebook.com/v20.0/me/messages"; // Instagram uses a slightly different endpoint pattern for graph
        
        return Http::withToken($token)->post($url, [
            'recipient' => ['id' => $to],
            'message' => ['text' => $message]
        ]);
    }
}
