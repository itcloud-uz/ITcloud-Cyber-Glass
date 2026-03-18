<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\AiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class GeminiAgentService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    public function handleIncomingMessage(string $agentType, string $message, string $chatId)
    {
        // Bu yerda aslida Gemini API ga so'rov ketishi kerak (System Prompt bilan birga)
        // Hozirgi bosqichda Function Calling mock qilingan.

        if ($agentType === 'sales') {
            if (str_contains(strtolower($message), 'sotib olaman')) {
                // Agent create_new_tenant(domain, plan) chaqiradi
                $domain = 'yangi-mijoz-' . rand(100, 999) . '.itcloud-obsidian.uz';
                return $this->createNewTenant($domain, 'Start Plan');
            }
            return "Assalomu alaykum! ITcloud xizmatlariga xush kelibsiz. Sotib olishni xohlaysizmi?";
        }

        if ($agentType === 'finance') {
            if (str_contains(strtolower($message), 'tolov qildim') || str_contains(strtolower($message), "to'lov qildim")) {
                // Agent extend_subscription(client_id, 30_days) chaqiradi
                $tenantId = Tenant::first()->id ?? 1; // namuna uchun birinchi mijoz
                return $this->extendSubscription($tenantId, 30);
            }
            return "Hurmatli mijoz, obunangiz vaqti tugamoqda. To'lovni shoshiling.";
        }

        if ($agentType === 'support') {
            return "Xayrli kun! Texnik yordam agentiman. Portalda qanday muammo bor?";
        }

        return "Noma'lum agent.";
    }

    public function createNewTenant(string $domain, string $planName)
    {
        // 1. Bazaga yozish
        $tenant = Tenant::create([
            'company_name' => 'Dynamic Client',
            'domain' => $domain,
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(3), // Trial period
        ]);

        $tenant->subscriptions()->create([
            'plan_name' => $planName,
            'duration_days' => 3,
            'amount_paid' => 0,
            'paid_at' => Carbon::now(),
        ]);

        // 2. AI Log yozish
        AiLog::create([
            'tenant_id' => $tenant->id,
            'agent_type' => 'sales',
            'action' => 'Auto-Deploy amalga oshirildi',
            'details' => "Mijoz talabiga binoan $domain manzilida yangi loyiha ochildi.",
        ]);

        // 3. (Kelajakda) Serverga bash/nginx zapros yuborib papka yaratish
        // shell_exec("make-tenant.sh $domain");
        
        return "Tabriklaymiz! Sizning CRM tizimingiz serverda avtomatik ko'tarildi. Ssilka: https://$domain";
    }

    public function extendSubscription(int $tenantId, int $days)
    {
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $currentExpires = $tenant->expires_at && Carbon::now()->lessThan($tenant->expires_at) ? $tenant->expires_at : Carbon::now();
            $tenant->expires_at = $currentExpires->addDays($days);
            $tenant->status = 'active'; // Agar blokda bo'lsa, blokdan ochiladi
            $tenant->save();

            $tenant->subscriptions()->create([
                'plan_name' => 'Renewal',
                'duration_days' => $days,
                'amount_paid' => 150000,
                'paid_at' => Carbon::now(),
            ]);

            AiLog::create([
                'tenant_id' => $tenant->id,
                'agent_type' => 'finance',
                'action' => 'Obuna uzaytirildi',
                'details' => "Mijoz hisobidan to'lov tushgach, tizim avtomatik $days kunga uzaytirildi.",
            ]);

            return "To'lov qabul qilindi! Hisobingiz faollashtirildi va obuna muddati uzaytirildi.";
        }
        return "Xatolik: Bunday mijoz topilmadi.";
    }
}
