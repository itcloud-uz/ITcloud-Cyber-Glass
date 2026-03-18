<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Actually, this middleware checks the database domain
        $host = $request->getHost();
        
        // Skip for the master API/admin dashboard itself if needed 
        // Typically the master API has its own domain, let's say 'itcloud.uz'
        // Let's assume anything other than localhost or master domain is a tenant check
        if ($host === 'localhost' || $host === 'itcloud-obsidian.uz' || $host === '127.0.0.1') {
            return $next($request);
        }

        $tenant = Tenant::where('domain', $host)->first();

        // If no tenant or tenant is blocked or tenant expired
        if (!$tenant || $tenant->status === 'blocked' || ($tenant->expires_at && Carbon::now()->greaterThanOrEqualTo($tenant->expires_at))) {
            return response()->view('blocked');
        }

        return $next($request);
    }
}
