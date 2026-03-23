<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\AiLog;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function handlePaymeWebhook(Request $request)
    {
        // Ushbu funksiya Payme yoki Click dan kelgan (Webhook) signallarni qabul qiladi
        $invoiceId = $request->input('invoice_id');
        $amount = $request->input('amount', 150000);
        $tenantId = $request->input('tenant_id', 1);

        $tenant = Tenant::find($tenantId);

        if ($tenant) {
            // 1. Obunani hisoblash (Reconcilation)
            $currentExpires = $tenant->expires_at && Carbon::now()->lessThan($tenant->expires_at) ? $tenant->expires_at : Carbon::now();
            $tenant->expires_at = $currentExpires->addDays(30);
            $tenant->status = 'active'; // Blokdan yechish
            $tenant->save();

            // 1. Zero-Touch AUTO-PROVISIONING ishga tushirish
            \App\Jobs\DeployTenantInstance::dispatch($tenant->id);

            // 1.1 Referral Bonus logic
            if ($tenant->referred_by_id) {
                $referrer = Tenant::find($tenant->referred_by_id);
                if ($referrer) {
                    $refExpires = $referrer->expires_at && Carbon::now()->lessThan($referrer->expires_at) ? $referrer->expires_at : Carbon::now();
                    $referrer->expires_at = $refExpires->addDays(30);
                    $referrer->save();

                    AiLog::create([
                        'tenant_id' => $referrer->id,
                        'agent_type' => 'finance',
                        'action' => 'Referral Bonus +30 kun',
                        'details' => "Siz taklif qilgan mijoz ({$tenant->company_name}) to'lov qildi. Tizimingizga 30 kun tekin qo'shib berildi!"
                    ]);
                }
            }

            // 2. Tranzaksiya qilib Subscription yozish
            $tenant->subscriptions()->create([
                'plan_name' => 'Payme Auto-Renewal',
                'duration_days' => 30,
                'amount_paid' => $amount,
                'paid_at' => Carbon::now(),
            ]);

            // 3. AI Agent nomidan Telegram yoki DB logiga yozish
            AiLog::create([
                'tenant_id' => $tenant->id,
                'agent_type' => 'finance',
                'action' => 'Webhook To\'lov ' . $amount,
                'details' => "Mijoz Payme orqali to'lov qildi. Tizim avtomatik ravishda 30 kunga faollashdi va blokdan ochildi.",
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'To\'lov qabul qilindi va tizim uzaytirildi!'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Mijoz topilmadi'], 404);
    }
}
