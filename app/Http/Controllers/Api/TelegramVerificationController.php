<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramVerificationController extends Controller
{
    public function checkStatus()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        return response()->json([
            'is_verified' => (bool)$user->is_verified,
            'status' => $user->is_verified ? 'verified' : 'pending'
        ]);
    }
}
