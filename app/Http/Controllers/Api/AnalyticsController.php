<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function dashboardData()
    {
        // 1. Revenue last 6 months
        $revenueData = Subscription::select(
            DB::raw('sum(amount_paid) as total'),
            DB::raw("strftime('%m', created_at) as month")
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // 2. Leads last 6 months
        $leadsData = Lead::select(
            DB::raw('count(*) as total'),
            DB::raw("strftime('%m', created_at) as month")
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // 3. Current Stats
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_leads' => Lead::count(),
            'new_leads_today' => Lead::whereDate('created_at', Carbon::today())->count(),
            'total_revenue' => Subscription::sum('amount_paid')
        ];

        return response()->json([
            'revenue' => $revenueData,
            'leads' => $leadsData,
            'stats' => $stats,
            'months' => $this->getLastSixMonths()
        ]);
    }

    private function getLastSixMonths()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('M');
        }
        return $months;
    }
}
