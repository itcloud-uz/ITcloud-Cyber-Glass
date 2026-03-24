<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClientSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAuthController extends Controller
{
    public function showLogin()
    {
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Xavfsizlik jurnali
            ClientSecurityLog::create([
                'user_id' => $user->id,
                'action' => 'Muvaffaqiyatli kirish',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Agar tasdiqlanmagan bo'lsa
            if (!$user->is_verified) {
                return redirect()->route('client.verify.telegram');
            }

            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors(['email' => 'Hato maʼlumotlar.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('client.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'client',
            'is_verified' => false,
            'verification_hash' => md5(Str::random(10) . time()),
        ]);

        Auth::login($user);

        // Xavfsizlik jurnali
        ClientSecurityLog::create([
            'user_id' => $user->id,
            'action' => 'Yangi ruyxatdan o\'tish',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('client.verify.telegram');
    }

    public function showVerifyTelegram()
    {
        $user = Auth::user();
        if ($user->is_verified) {
            return redirect()->route('client.dashboard');
        }
        return view('client.auth.verify-telegram', compact('user'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/client/login');
    }

    public function forgotPassword()
    {
        return view('client.auth.forgot-password');
    }
}
