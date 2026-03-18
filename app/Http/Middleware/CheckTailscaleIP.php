<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTailscaleIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // 100.x.x.x is Tailscale's CGNAT IPv4 space.
        // We also mock 127.0.0.1 so it runs locally for development.
        if ($ip !== '127.0.0.1' && !str_starts_with($ip, '100.')) {
            // For extra security, don't even say "Unauthorized", just abort.
            abort(403, 'Access denied. You are not on the Tailscale VPN.');
        }

        return $next($request);
    }
}
