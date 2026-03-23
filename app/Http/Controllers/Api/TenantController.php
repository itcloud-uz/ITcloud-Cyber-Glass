<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

class TenantController extends Controller
{
    public function store(Request $request)
    {
        $referredBy = null;
        if ($request->referral_code) {
            $referredBy = Tenant::where('referral_code', $request->referral_code)->first();
        }

        $tenant = Tenant::create([
            'company_name' => $request->company_name,
            'domain' => $request->domain,
            'referral_code' => 'IT' . strtoupper(substr(md5(uniqid()), 0, 6)),
            'referred_by_id' => $referredBy ? $referredBy->id : null,
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        return response()->json(['status' => 'success', 'tenant' => $tenant]);
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($request->only('company_name', 'domain', 'custom_domain'));
        
        if ($request->custom_domain) {
            \App\Services\DomainService::setupCustomDomain($tenant, $request->custom_domain);
        }
        
        return response()->json(['status' => 'success']);
    }

    public function changeStatus(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = $request->status; // active or blocked
        $tenant->save();
        return response()->json(['status' => 'success']);
    }

    public function addSubscription(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $duration = $request->duration; // days or infinity
        
        if ($duration === 'infinity') {
            $tenant->expires_at = Carbon::now()->addYears(100);
            $tenant->status = 'active';
        } else {
            $currentExpires = $tenant->expires_at && Carbon::now()->lessThan($tenant->expires_at) ? $tenant->expires_at : Carbon::now();
            $tenant->expires_at = $currentExpires->addDays((int)$duration);
            $tenant->status = 'active';
        }
        $tenant->save();

        $tenant->subscriptions()->create([
            'plan_name' => 'Qo\'lda Uzaytirish',
            'duration_days' => $duration === 'infinity' ? 9999 : (int)$duration,
            'amount_paid' => $request->amount ?? 0,
            'paid_at' => Carbon::now(),
        ]);

        return response()->json(['status' => 'success']);
    }
    public function destroy($id)
    {
        Tenant::destroy($id);
        return response()->json(['status' => 'success']);
    }
}
