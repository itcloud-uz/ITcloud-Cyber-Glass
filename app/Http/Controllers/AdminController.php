<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\AiLog;

class AdminController extends Controller
{
    public function index()
    {
        $activeTenantsCount = Tenant::where('status', 'active')->count();
        $blockedTenantsCount = Tenant::where('status', 'blocked')->count();
        
        // Let's assume ai sales are recent subscriptions
        $aiSalesCount = Subscription::whereMonth('created_at', now()->month)->count();
        
        $aiSavedTime = AiLog::count() * 0.5; // Roughly 30 mins saved per task
        
        $tenants = Tenant::orderBy('status', 'asc')->orderBy('expires_at', 'asc')->get();
        $aiLogs = AiLog::latest()->take(10)->get();
        
        $templates = \App\Models\Template::all();
        $securityLogs = \App\Models\SecurityLog::latest()->take(20)->get();
        $telegramBots = \App\Models\TelegramBot::all();
        $leads = \App\Models\Lead::latest()->get();

        return view('welcome', compact(
            'activeTenantsCount',
            'blockedTenantsCount',
            'aiSalesCount',
            'aiSavedTime',
            'tenants',
            'aiLogs',
            'templates',
            'securityLogs',
            'telegramBots',
            'leads'
        ));
    }
}
