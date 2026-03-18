<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFaceId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce on authenticated users if they haven't verified Face ID
        if (auth()->check() && !$request->session()->get('face_id_verified')) {
            // Force them to complete Face ID step by redirecting to login page indicating face ID is pending
            return redirect()->route('login')->with('show_face_id', true);
        }

        return $next($request);
    }
}
