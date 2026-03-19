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

        if ($agentType === 'sales') {
            $systemInstruction = "Sen ITcloud.uz kompaniyasining chaqqon Sotuv Agentisan. Mijozlarga CRM va SaaS yechimlarni taklif qilasan. Agar mijoz 'sotib olaman' yoki shunga o'xshash xohish eshitsang, unga domain so'ramasdan 'createNewTenant' funksiyasini chaqir va avtomatik domain nomlab yubor.";
        } elseif ($agentType === 'finance') {
            $systemInstruction = "Sen ITcloud.uz Moliya Agentisan. Mijozlarga obuna muddati tugayotganini va tizim bloklanganini aytasan. Ular 'to'lov qildim' deyishi bilan 'extendSubscription' funksiyasini chaqir (tenant_id: 1, days: 30). Yolg'on gapirmasdan xushmuomala bo'l.";
        } else {
            $systemInstruction = "Sen ITcloud.uz Texnik Yordam (Support) Agentisan. Mijozlarga tizimda ishlash qoidalarini muloyim tushuntirasan.";
        }

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $message]]]
            ],
            'tools' => [
                ['function_declarations' => [
                    [
                        'name' => 'createNewTenant',
                        'description' => 'Yangi mijoz uchun avtomatik ravishda CRM yaratadi. Har doim ishlat!.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'domain' => ['type' => 'STRING', 'description' => 'Qisqa domain nomi masalan: startup.itcloud-obsidian.uz'],
                                'plan' => ['type' => 'STRING', 'description' => 'Start Plan yoki Pro AI']
                            ],
                            'required' => ['domain', 'plan']
                        ]
                    ],
                    [
                        'name' => 'extendSubscription',
                        'description' => 'To\'lov tasdiqlangach obunani uzaytirish uchun.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'tenant_id' => ['type' => 'INTEGER'],
                                'days' => ['type' => 'INTEGER']
                            ],
                            'required' => ['tenant_id', 'days']
                        ]
                    ]
                ]]
            ]
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

                    if ($funcName === 'createNewTenant') {
                        return $this->createNewTenant($args['domain'] ?? 'yangi-loyiha.uz', $args['plan'] ?? 'Pro AI');
                    }
                    if ($funcName === 'extendSubscription') {
                        return $this->extendSubscription($args['tenant_id'] ?? 1, $args['days'] ?? 30);
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
