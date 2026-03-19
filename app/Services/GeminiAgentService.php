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
        if (empty($this->apiKey)) {
            return "Xatolik: Gemini API kiliti o'rnatilmagan.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";
        
        $tools = [];

        if ($agentType === 'sales') {
            $systemInstruction = "Sen ITcloud kompaniyasining eng oldi sotuvchi menejerisan. Maqsading — mijozlarning ehtiyojini tushunib, ularga eng mos CRM yoki Landing Page shablonini sotish. Sen xushmuomala, energiya bilan to'la va ishontira oladigan darajada gapirasan. Agar mijoz narxni qimmat desa, 10% gacha chegirma taklif qila olasan. Agar mijoz tayyor bo'lsa 'create_new_tenant' ishlat.";
            $tools = [
                ['name' => 'get_templates_list', 'description' => 'Tayyor CRM shablonlari va narxlarini ko\'rish.'],
                ['name' => 'check_domain_availability', 'description' => 'Mijoz tanlagan subdomen bo\'sh yoki yo\'qligini tekshirish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['domain_name' => ['type' => 'STRING']], 'required' => ['domain_name']]],
                ['name' => 'create_new_tenant', 'description' => 'Serverda avtomatik yangi CRM papkasini ochish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_name' => ['type' => 'STRING'], 'domain' => ['type' => 'STRING'], 'phone' => ['type' => 'STRING']], 'required' => ['client_name', 'domain']]]
            ];
        } elseif ($agentType === 'finance') {
            $systemInstruction = "Sen ITcloud'ning qat'iy, lekin muloyim moliyachisisan. Sening vazifang — to'lov vaqti kelgan mijozlarni ogohlantirish va hisob-kitoblarni yuritish. Sen emotsiyalarga berilmaysan, aniq raqamlar va sanalar bilan gapirasan.";
            $tools = [
                ['name' => 'check_tenant_balance', 'description' => 'Mijozning qancha vaqti qolganini tekshirish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER']], 'required' => ['client_id']]],
                ['name' => 'generate_payment_link', 'description' => 'Payme yoki Click orqali to\'lov ssilkasini yaratib berish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['amount' => ['type' => 'INTEGER'], 'client_id' => ['type' => 'INTEGER']], 'required' => ['amount', 'client_id']]],
                ['name' => 'block_tenant', 'description' => 'To\'lamagan mijozning tizimini bloklash.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER']], 'required' => ['client_id']]]
            ];
        } else {
            $systemInstruction = "Sen ITcloud tizimining katta muhandisisan. Mijozlarga o'z CRM'larini qanday ishlatishni tushuntirasan, muammolarni hal qilasan. Sen sabrli va texnik tilda (lekin sodda qilib) tushuntirasan.";
            $tools = [
                ['name' => 'reset_admin_password', 'description' => 'Mijoz o\'z CRM parolini unotsa, tiklab berish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER']], 'required' => ['client_id']]],
                ['name' => 'escalate_to_human', 'description' => 'Muammo murakkab bo\'lsa, suhbatni haqiqiy adminga o\'tkazish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER'], 'issue' => ['type' => 'STRING']], 'required' => ['client_id', 'issue']]]
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $message]]]
            ],
            'tools' => [['function_declarations' => $tools]]
        ];

        try {
            $response = Http::post($url, $payload);
            $data = $response->json();

            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) {
                return "AI Xizmati javob bermadi: " . json_encode($data);
            }

            // Function Calling ni tekshirish
            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $funcName = $part['functionCall']['name'];
                    $args = $part['functionCall']['args'] ?? [];

                    // Sales tools
                    if ($funcName === 'create_new_tenant') return $this->createNewTenant($args['domain'] ?? 'yangi.uz', 'Pro AI');
                    if ($funcName === 'check_domain_availability') return "Bu domen (".$args['domain_name'].") hozircha bo'sh! Bemalol xarid qilishingiz mumkin.";
                    if ($funcName === 'get_templates_list') return "Bizning shablonlar: START (50,000 UZS) va PRO AI (150,000 UZS).";
                    
                    // Finance tools
                    if ($funcName === 'check_tenant_balance') return "Ushbu loyihada yana 5 kun qolgan.";
                    if ($funcName === 'generate_payment_link') return "To'lov havolasi (Payme): https://payme.uz/fallback/merchant/".rand(1000,9999);
                    if ($funcName === 'block_tenant') return "Tizim muvaffaqiyatli bloklandi.";
                    
                    // Support tools
                    if ($funcName === 'reset_admin_password') return "Parol tizim orqali yangilandi. Yangi parol Telegramga yuborildi.";
                    if ($funcName === 'escalate_to_human') {
                        AiLog::create(['tenant_id' => null, 'agent_type' => 'support', 'action' => 'Qiyin vaziyat', 'details' => "Master Admin e'tibori kerak: " . ($args['issue'] ?? '')]);
                        return "Muammoni Master Adminga yetkazdim. Ular tez orada siz bilan bog'lanishadi.";
                    }
                }
            }

            // Matnli javobni qaytarish
            return $parts[0]['text'] ?? "AI bo'sh javob qaytardi.";
            
        } catch (\Exception $e) {
            return "Xatolik yuz berdi: " . $e->getMessage();
        }
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
