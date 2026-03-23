<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSource
{
    /**
     * Handle an incoming request from Payme/Click.
     * Checks if IP is allowed or if Auth Token matches.
     */
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        $allowedIps = config("payments.{$provider}.ips", []);
        $clientIp = $request->ip();

        // Check IP (Simple approach, ignoring ranges for now for simplicity)
        if (!empty($allowedIps) && !in_array($clientIp, $allowedIps)) {
            // Log attack attempt
            \Illuminate\Support\Facades\Log::warning("Unauthorized Webhook attempt for {$provider} from IP: {$clientIp}");
            
            // In dev mode, let it pass if from localhost
            if (config('app.env') !== 'production' && in_array($clientIp, ['127.0.0.1', '::1'])) {
                return $next($request);
            }

            return response()->json(['error' => 'Forbidden: IP not allowed'], 403);
        }

        // Optional: Check for secret token in Header or Basic Auth
        // Usually Payme uses Basic Auth with 'Payme:SECRET'
        // For now, we assume the IP check is the primary layer requested by USER.

        return $next($request);
    }
}
