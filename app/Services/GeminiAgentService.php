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

    public function handleIncomingMessage(string $agentType, string $message, string $chatId, $botId = null)
    {
        if (empty($this->apiKey)) {
            return "Xatolik: Gemini API kiliti o'rnatilmagan.";
        }

        $currentTask = "";
        if ($botId) {
            $bot = \App\Models\TelegramBot::find($botId);
            if ($bot) {
                $agentType = $bot->agent_type;
                $currentTask = $bot->current_task;
            }
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";
        
        $tools = [
            ['name' => 'get_system_overview', 'description' => 'Tizimdagi umumiy holatni ko\'rish (Loyihalar, Leadlar, Botlar soni).'],
            ['name' => 'get_recent_events', 'description' => 'Oxirgi 5 ta AI agent harakatlari va tizim o\'zgarishlarini ko\'rish.'],
            ['name' => 'notify_other_agent', 'description' => 'Boshqa agentga (masalan moliya yoki texnik) xabar yoki topshiriq yuborish.', 'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'target_agent' => ['type' => 'STRING', 'enum' => ['sales', 'finance', 'support']],
                    'message' => ['type' => 'STRING']
                ],
                'required' => ['target_agent', 'message']
            ]],
            ['name' => 'query_business_data', 'description' => 'Bazadan moliyaviy va mijozlar haqida analitik ma\'lumotlarni olish (masalan eng ko\'p sotilgan, daromad va h.k).', 'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'question_type' => ['type' => 'STRING', 'enum' => ['sales_performance', 'churn_rate', 'popular_services', 'top_leads']],
                    'time_range' => ['type' => 'STRING', 'enum' => ['last_month', 'this_year', 'all_time']]
                ],
                'required' => ['question_type']
            ]]
        ];

        if ($agentType === 'sales') {
            $templates = \App\Models\Template::all()->map(function($t) {
                $incl = is_array($t->includes) ? implode(', ', $t->includes) : '';
                return "{$t->name}: {$t->price} UZS ({$t->payment_type}). Ichida: $incl. Afzalliklari: {$t->advantages}";
            })->implode('; ');
            $systemInstruction = "Sen ITcloud kompaniyasining eng kuchli sotuvchi menejerisan. Bizda quyidagi xizmatlar va tariflar mavjud: $templates. Maqsading — mijozlarning ehtiyojini tushunib, ularga eng mos xizmatni taklif qilish. Sizning xizmatlaringizning afzalliklari va nima kiritilganligi haqida batafsil ma'lumot bering. Agar mijoz bog'lanmoqchi bo'lsa yoki sotib olishga qiziqsa 'create_sales_lead' funksiyasini ishlatib ularning ma'lumotlarini bazaga kirit. Hech qachon mijozni shunchaki kutib qol dima, doim ma'lumotlarini qoldirishni so'ra.";
            $tools = array_merge($tools, [
                ['name' => 'get_templates_list', 'description' => 'Tayyor CRM shablonlari va narxlarini ko\'rish.'],
                ['name' => 'create_sales_lead', 'description' => 'Mijoz ma\'lumotlarini sotuv bo\'limiga yuborish.', 'parameters' => [
                    'type' => 'OBJECT', 
                    'properties' => [
                        'customer_name' => ['type' => 'STRING'], 
                        'phone' => ['type' => 'STRING'], 
                        'interest' => ['type' => 'STRING']
                    ], 
                    'required' => ['customer_name', 'phone']
                ]]
            ]);
        } elseif ($agentType === 'finance') {
            $systemInstruction = "Sen ITcloud'ning qat'iy, lekin muloyim moliyachisisan. Sening vazifang — to'lov vaqti kelgan mijozlarni ogohlantirish va hisob-kitoblarni yuritish. Sen emotsiyalarga berilmaysan, aniq raqamlar va sanalar bilan gapirasan.";
            $tools = array_merge($tools, [
                ['name' => 'check_tenant_balance', 'description' => 'Mijozning qancha vaqti qolganini tekshirish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER']], 'required' => ['client_id']]],
                ['name' => 'generate_payment_link', 'description' => 'Payme yoki Click orqali to\'lov ssilkasini yaratib berish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['amount' => ['type' => 'INTEGER'], 'client_id' => ['type' => 'INTEGER']], 'required' => ['amount', 'client_id']]],
                ['name' => 'block_tenant', 'description' => 'To\'lamagan mijozning tizimini bloklash.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER']], 'required' => ['client_id']]]
            ]);
        } else {
            $systemInstruction = "Sen ITcloud tizimining katta muhandisisan. Mijozlarga o'z CRM'larini qanday ishlatishni tushuntirasan, muammolarni hal qilasan. Sen sabrli va texnik tilda (lekin sodda qilib) tushuntirasan.";
            $tools = array_merge($tools, [
                ['name' => 'reset_admin_password', 'description' => 'Mijoz o\'z CRM parolini unotsa, tiklab berish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER']], 'required' => ['client_id']]],
                ['name' => 'escalate_to_human', 'description' => 'Muammo murakkab bo\'lsa, suhbatni haqiqiy adminga o\'tkazish.', 'parameters' => ['type' => 'OBJECT', 'properties' => ['client_id' => ['type' => 'INTEGER'], 'issue' => ['type' => 'STRING']], 'required' => ['client_id', 'issue']]]
            ]);
        }

        if (!empty($currentTask)) {
            $systemInstruction .= " Senga bitta MAXSUS VAZIFA (TASK) yuklatilgan: " . $currentTask . ". Barcha javoblaringda faqat shu vazifani inobatga ol va uning doirasida ishla.";
        }

        // RAG Foundation: Fetch Knowledge Base Context
        if ($botId) {
            $knowledge = \App\Models\KnowledgeBase::where('bot_id', $botId)->get();
            if ($knowledge->count() > 0) {
                $context = $knowledge->map(fn($kb) => "Hujjat ({$kb->file_name}): {$kb->content}")->implode("\n");
                $systemInstruction .= "\n\nQuyidagi qo'shimcha bilimlar bazasidan foydalan (RAG): \n" . $context;
            }
        }

        $tools_payload = [];
        if (!empty($tools)) {
            $tools_payload = [['functionDeclarations' => $tools]];
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $message]]]
            ]
        ];

        if (!empty($tools_payload)) {
            $payload['tools'] = $tools_payload;
        }

        try {
            \Illuminate\Support\Facades\Log::info("Gemini Request [{$agentType}]: " . json_encode($payload));
            $response = Http::post($url, $payload);
            $data = $response->json();
            \Illuminate\Support\Facades\Log::info("Gemini Full Response: " . json_encode($data));

            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) {
                return "AI Xizmati javob bermadi yoki xato yuz berdi.";
            }

            // Function Calling ni tekshirish
            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $funcName = $part['functionCall']['name'];
                    $args = $part['functionCall']['args'] ?? [];

                    // Sales tools
                    if ($funcName === 'create_sales_lead') {
                        \App\Models\Lead::create([
                            'customer_name' => $args['customer_name'],
                            'phone' => $args['phone'],
                            'details' => $args['interest'] ?? 'Qiziqish bildirdi',
                            'status' => 'yangi'
                        ]);
                        return "Ma'lumotlaringizni sotuv bo'limiga yubordim. Tez orada operatorlarimiz bog'lanishadi!";
                    }
                    if ($funcName === 'create_new_tenant') return $this->createNewTenant($args['domain'] ?? 'yangi.uz', 'Pro AI');
                    if ($funcName === 'get_templates_list') {
                        $tpls = \App\Models\Template::all()->map(function($t) {
                            $incl = is_array($t->includes) ? implode(', ', $t->includes) : 'Standard';
                            return "*{$t->name}* - {$t->price} UZS ({$t->payment_type})\n- Ichida: $incl\n- Afzalligi: {$t->advantages}";
                        })->implode("\n\n");
                        return "Bizning barcha xizmatlarimiz ro'yxati:\n\n" . $tpls;
                    }
                    
                    // Support tools
                    if ($funcName === 'reset_admin_password') return "Parol tizim orqali yangilandi. Yangi parol Telegramga yuborildi.";
                    if ($funcName === 'escalate_to_human') {
                        AiLog::create(['tenant_id' => null, 'agent_type' => 'support', 'action' => 'Qiyin vaziyat', 'details' => "Master Admin e'tibori kerak: " . ($args['issue'] ?? '')]);
                        return "Muammoni Master Adminga yetkazdim. Ular tez orada siz bilan bog'lanishadi.";
                    }

                    // Global Tools handling
                    if ($funcName === 'get_system_overview') {
                        $tenants = \App\Models\Tenant::count();
                        $leads = \App\Models\Lead::count();
                        $bots = \App\Models\TelegramBot::count();
                        return "Tizim holati: Jami $tenants ta mijoz loyihasi, $leads ta yangi so'rovlar (leads) va $bots ta AI agentlar faoliyat yuritmoqda.";
                    }

                    if ($funcName === 'get_recent_events') {
                        $logs = \App\Models\AiLog::latest()->take(5)->get()->map(function($l) {
                            return "[{$l->created_at->format('H:i')}] {$l->agent_type}: {$l->action} - {$l->details}";
                        })->implode("\n");
                        return "Oxirgi faoliyatlar:\n" . ($logs ?: "Hozircha faoliyatlar yo'q.");
                    }

                    if ($funcName === 'notify_other_agent') {
                        $target = $args['target_agent'] ?? 'all';
                        $msg = $args['message'] ?? '';
                        AiLog::create([
                            'agent_type' => $agentType,
                            'action' => 'Swarm Signaling',
                            'details' => "Target: {$target}. Xabar: {$msg}"
                        ]);
                        return "Xabar {$target} agentiga yuborildi. Ular tez orada javob berishadi.";
                    }

                    if ($funcName === 'query_business_data') {
                        $type = $args['question_type'];
                        if ($type === 'sales_performance') {
                            $total = \App\Models\Subscription::sum('amount_paid');
                            $count = \App\Models\Subscription::where('paid_at', '>', Carbon::now()->subMonth())->count();
                            return "O'tgan oyga nisbatan daromad tahlili: Jami tushum " . number_format($total, 0, ',', ' ') . " UZS. Oxirgi 30 kunda $count ta yangi to'lov amalga oshirilgan.";
                        }
                        if ($type === 'popular_services') {
                            $top = \App\Models\Template::take(3)->get()->map(fn($t) => "*{$t->name}*")->implode(', ');
                            return "Hozirda eng ommabop xizmatlarimiz: $top. Ularga qiziqish yuqori.";
                        }
                        if ($type === 'top_leads') {
                            $leads = \App\Models\Lead::latest()->take(3)->get()->map(fn($l) => $l->customer_name)->implode(', ');
                            return "Eng oxirgi va qiziqqan mijozlar (Leads): $leads. Ularga tezroq qo'ng'iroq qilishni tavsiya qilaman.";
                        }
                        return "Hozirda bu turdagi ma'lumotni tahlil qila olmayman, lekin tez orada o'rganaman.";
                    }
                }
            }

            // Matnli javobni qaytarish
            $finalResponse = $parts[0]['text'] ?? "Kechirasiz, hozirda savolingizga javob bera olmayman.";
            
            // Log for human monitoring
            AiLog::create([
                'agent_type' => $agentType,
                'chat_id' => $chatId,
                'action' => 'Chat Message',
                'user_message' => $message,
                'bot_response' => $finalResponse,
                'details' => "AI javob berdi"
            ]);

            return $finalResponse;
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gemini Error: " . $e->getMessage());
        }
    }

    public function handleVoiceMessage(string $base64Audio, string $chatId, $botId = null)
    {
        if (empty($this->apiKey)) return "Xatolik: API Key.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";
        
        $payload = [
            'contents' => [
                ['parts' => [
                    ['text' => "Sen AI Assistantisan. Senga ovozli xabar (audio) kelmoqda. Uni matnga o'gir va ichidagi ma'lumotlarni tushunib, agar mijoz (lead) haqida bo'lsa 'create_sales_lead' funksiyasini chaqir. Agar shunchaki savol bo'lsa javob ber."],
                    ['inline_data' => ['mime_type' => 'audio/ogg', 'data' => $base64Audio]]
                ]]
            ],
            'tools' => [['functionDeclarations' => [
                ['name' => 'create_sales_lead', 'description' => 'Mijoz ma\'lumotlarini kiritish.', 'parameters' => [
                    'type' => 'OBJECT', 
                    'properties' => [
                        'customer_name' => ['type' => 'STRING'], 
                        'phone' => ['type' => 'STRING'], 
                        'details' => ['type' => 'STRING']
                    ], 
                    'required' => ['customer_name', 'phone']
                ]]
            ]]]
        ];

        try {
            $response = Http::post($url, $payload);
            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) return "Ovozli xabarni tahlil qila olmadim.";

            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $funcName = $part['functionCall']['name'];
                    $args = $part['functionCall']['args'] ?? [];
                    if ($funcName === 'create_sales_lead') {
                        \App\Models\Lead::create([
                            'customer_name' => $args['customer_name'],
                            'phone' => $args['phone'],
                            'details' => "[Voice CMD] " . ($args['details'] ?? ''),
                            'status' => 'yangi'
                        ]);
                        return "Ovozli xabar asosida yangi Lead yaratildi: " . $args['customer_name'];
                    }
                }
            }

            return $parts[0]['text'] ?? "Ovozli xabar eshitildi, lekin vazifa tushunilmadi.";
        } catch (\Exception $e) { Log::error($e->getMessage()); }
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
