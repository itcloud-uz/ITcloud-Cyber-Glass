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
