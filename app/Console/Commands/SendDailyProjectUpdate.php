<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;
use App\Models\AiProject;
use App\Models\Setting;
use App\Models\TelegramBot;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

class SendDailyProjectUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-project-update {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily statistics and PR update via AI to Telegram channel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for scheduled PR Bots...");
        
        $currentMinute = Carbon::now('Asia/Tashkent')->format('H:i');
        
        // Find all active PR channel bots
        $query = TelegramBot::where('agent_type', 'pr_channel')->where('is_active', 1);
        
        if (!$this->option('force')) {
            $query->where('schedule_time', $currentMinute);
        }

        $bots = $query->get();
            
        if ($bots->isEmpty()) {
            $this->info("No PR bots scheduled for $currentMinute.");
            return;
        }
        
        $newLeads = Lead::whereDate('created_at', today())->count();
        $activeProjects = AiProject::where('status', 'deployed')->count();
        $totalTenants = Tenant::count();
        
        $rawData = "Bugungi statistika: Yangi mijozlar ulanishi (Leads) - {$newLeads} ta, Dasturlangan va muvaffaqiyatli topshirilgan loyihalar (Deployed Projects) - {$activeProjects} ta, Jami B2B Mijozlar - {$totalTenants} ta. ITcloud tizimining server barqarorligi: 100%.";
        
        $apiKey = 'AIzaSyCzamwAJ2myvYf_JvuAXIjj2gbpT_SAz6g';
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

        foreach ($bots as $bot) {
            $this->info("Processing PR Bot ID: {$bot->id} for Channel: {$bot->channel_id} at $currentMinute");

            $promptAddition = $bot->custom_prompt ?? "Texnologiya va Cyber Security haqida gapiring.";
            $theme = $bot->theme ?? 'cyberpunk';

            $prompt = "Sen malakali PR menejersan. Quyidagi quruq ma'lumotlardan foydalanib, Telegram kanal uchun qiziqarli, o'qishli va mijozlarni jalb qiluvchi post yozib ber: [ $rawData ]. Eslatma: $promptAddition. Emoji lardan xushmuomala foydalan.";

            try {
                $response = Http::post($apiUrl, [
                    'systemInstruction' => [
                        'parts' => [['text' => "Sen malakali PR menejersan. Maqsading — berilgan ma'lumotlar va maxsus ko'rsatma asosida Telegram kanal uchun o'quvchini jalb qiluvchi, kreativ va professional post tayyorlash. Emoji lardan o'rinli foydalan."]]
                    ],
                    'contents' => [
                        ['parts' => [['text' => "Mana bizning bugungi statistika: " . $rawData . "\n\nSenga berilgan MAXSUS KO'RSATMA (Buni albatta bajar): " . $promptAddition]]]
                    ]
                ]);
                $data = $response->json();
                $postText = $data['candidates'][0]['content']['parts'][0]['text'] ?? "🚀 ITcloud'da bugun ajoyib kun! Tizimlarimiz barqaror ishlab kelmoqda.";
                
                $postText = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $postText);
                $postText = preg_replace('/\*(.*?)\*/', '<i>$1</i>', $postText);
            } catch (\Exception $e) {
                $this->error("Gemini xatosi: " . $e->getMessage());
                $postText = "🚀 ITcloud'da bugun ajoyib kun! Tizimlarimiz barqaror ishlab kelmoqda.";
            }

            // Image Theme Selection Custom per Bot
            $keywords = 'cyberpunk,technology,matrix';
            if ($theme === 'corporate') $keywords = 'business,futuristic,skyscraper,glass';
            if ($theme === 'minimal') $keywords = 'minimal,white,technology,abstract';
            if ($theme === 'medical_ai') $keywords = 'medical,lab,dna,blue,technology';
            
            $images = [
                'cyberpunk' => [
                    'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1614729939124-032f0b56c9ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'
                ],
                'corporate' => [
                    'https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'
                ],
                'minimal' => [
                    'https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'
                ],
                'medical_ai' => [
                    'https://images.unsplash.com/photo-1530497610245-94d3c16cda28?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'
                ]
            ];

            $selectedImages = $images[$theme] ?? $images['cyberpunk'];
            $imageUrl = $selectedImages[array_rand($selectedImages)];
            
            if ($bot->token && $bot->channel_id) {
                $tgResponse = Http::post("https://api.telegram.org/bot{$bot->token}/sendPhoto", [
                    'chat_id' => $bot->channel_id,
                    'photo' => $imageUrl,
                    'caption' => $postText,
                    'parse_mode' => 'HTML'
                ]);

                if ($tgResponse->successful()) {
                    $this->info("✅ Update sent to channel {$bot->channel_id} using bot {$bot->name}!");
                } else {
                    $this->error("❌ Telegram API fail for {$bot->name}: " . $tgResponse->body());
                }
            } else {
                $this->error("❌ Bot {$bot->name} is missing token or channel_id.");
            }
        }
    }
}
