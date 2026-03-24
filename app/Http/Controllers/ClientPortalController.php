<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Template;
use App\Models\ClientSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Mijozning faol loyihalari (Tenants)
        $projects = Tenant::where('user_id', $user->id)->get();
        
        // ITcloud do'koni (Templates)
        $marketStore = Template::all();

        return view('client.dashboard', compact('projects', 'marketStore'));
    }

    public function security()
    {
        $user = Auth::user();
        
        // Oxirgi harakatlar
        $logs = ClientSecurityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('client.security', compact('user', 'logs'));
    }

    public function ssoLogin(Tenant $tenant)
    {
        $user = Auth::user();

        // Xavfsizlik tekshiruvi: Bu loyiha haqiqatdan mijozniki ekanligini tekshirish
        if ($tenant->user_id !== $user->id) {
            return abort(403);
        }

        // SSO Token yaratiladi (Masalan, 5 daqiqa amal qiladigan)
        $ssoToken = Str::random(64);
        
        // Tizim jurnali uchun
        ClientSecurityLog::create([
            'user_id' => $user->id,
            'action' => "Loyihaga SSO orqali kirish: {$tenant->company_name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Mijozning o'z CRMiga yo'naltirish (Token bilan)
        return redirect()->away("https://{$tenant->domain}/auth/sso?token={$ssoToken}");
    }
}
