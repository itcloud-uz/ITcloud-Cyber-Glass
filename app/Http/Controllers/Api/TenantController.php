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
        $tenant = Tenant::create([
            'company_name' => $request->company_name,
            'domain' => $request->domain,
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        return response()->json(['status' => 'success', 'tenant' => $tenant]);
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($request->only('company_name', 'domain'));
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
