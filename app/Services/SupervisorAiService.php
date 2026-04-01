<?php

namespace App\Services;

use App\Models\AiLog;
use App\Models\Tenant;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class SupervisorAiService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    /**
     * Tizim holatini to'liq tahlil qilish va Supervisor hisobotini tayyorlash.
     */
    public function generateSystemReport()
    {
        if (empty($this->apiKey)) {
            return "Xatolik: Gemini API Key o'rnatilmagan.";
        }

        // Ma'lumotlarni yig'ish
        $tenantCount = Tenant::count();
        $leadCount = Lead::count();
        $recentLogs = AiLog::latest()->take(20)->get();
        $failedPayments = DB::table('academy_payments')->where('status', 'pending')->where('created_at', '<', Carbon::now()->subHours(6))->count();
        $activeMentors = DB::table('academy_mentors')->where('is_active', true)->count();
        $studentCount = DB::table('academy_progress')->count();
        $avgScore = DB::table('academy_results')->avg('score') ?? 0;
        $totalFeedback = DB::table('academy_results')->whereNotNull('ai_feedback')->count();

        // Loglarni formatlash
        $logSummary = $recentLogs->map(function($l) {
            return "[{$l->created_at->format('H:i')}] Agent: {$l->agent_type}, Action: {$l->action}, Details: {$l->details}";
        })->implode("\n");

        $prompt = "Sen 'ITcloud Master Supervisor' AI agentisan. Sening vazifang - butun tizimni (AI agentlar, o'qituvchilar, moliyaviy va texnik holat) tahlil qilish va nazorat qilish. 
        
        Tizim ma'lumotlari:
        - Mijozlar (Tenants): $tenantCount
        - Yangi so'rovlar (Leads): $leadCount
        - Faol mentorlar: $activeMentors
        - O'quvchilar soni: $studentCount
        - O'rtacha o'zlashtirish ko'rsatkichi (Score): $avgScore %
        - Mentorlar tomonidan berilgan feedbacklar soni: $totalFeedback
        - To'lovdan o'tmagan/kutilayotgan arizalar: $failedPayments
        
        AI Loglar (oxirgi 20 ta):
        $logSummary
        
        Iltimos, ushbu ma'lumotlar asosida:
        1. Tizimdagi xavf-xatarlar yoki anomaliyalar bormi? (Agent javoblari, tizim xatoliklari)
        2. O'quvchilar va mentorlar o'rtasidagi aloqa samaradorligi qanday? (Score va feedbacklar tahlili)
        3. Admin uchun 3 ta strategik tavsiya ber.
        
        Javobni professional, tahliliy va faqat Uzbek tilida yoz.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);
            
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "AI hisoboti tayyorlashda xatolik.";
        } catch (\Exception $e) {
            return "Supervisor Xatoligi: " . $e->getMessage();
        }
    }

    /**
     * Boshqa agentlarning xatti-harakatlarini monitoring qilish.
     */
    public function monitorAgent(string $agentType, string $action, string $details)
    {
        // Kelajakda bu yerda agent xatti-harakatlarini baholash (Safety filter)
        // Agar xavfli bo'lsa, ogohlantirish yoki to'xtatish mumkin.
        return true;
    }
}
