<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AcademyController extends Controller
{
    private $telegramBotToken = '8295962421:AAF3uH3did42i14YPZPMYqkrKDfHy8VlTKE';
    private $chatId = '-1003887827729';
    private $geminiKey = 'AIzaSyCzamwAJ2myvYf_JvuAXIjj2gbpT_SAz6g';

    // Talabalar uchun Dashboard
    public function getStudentDashboard(Request $request)
    {
        $user = auth()->user();
        $progress = DB::table('academy_progress')->where('user_id', $user->id)->first();
        $tasks = DB::table('academy_tasks')->orderBy('created_at', 'desc')->take(5)->get();
        $courses = DB::table('academy_courses')->where('is_active', true)->orderBy('order')->get();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'progress' => $progress,
            'tasks' => $tasks,
            'courses' => $courses
        ]);
    }

    // Kursga ariza topshirish
    public function apply(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:academy_applications,email',
            'phone' => 'required',
            'direction' => 'required',
            'level' => 'required'
        ]);

        $token = Str::random(40);
        $app = DB::table('academy_applications')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location' => $request->location,
            'direction' => $request->direction,
            'level' => $request->level,
            'status' => 'pending',
            'access_token' => $token,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Telegramga xabar yuborish
        $this->notifyTelegram("🎓 Yangi o'quvchi: {$request->name}\nYo'nalish: {$request->direction}\nDarajasi: {$request->level}\nEmail: {$request->email}");

        // AI tahlili (Fon rejimida ishlatish uchun)
        $this->processAiReview($app);

        return response()->json(['status' => 'success', 'message' => 'Ariza qabul qilindi. AI agent tahlil qilmoqda.']);
    }

    private function notifyTelegram($message)
    {
        Http::post("https://api.telegram.org/bot{$this->telegramBotToken}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);
    }

    private function processAiReview($appId)
    {
        $app = DB::table('academy_applications')->where('id', $appId)->first();
        
        // Gemini orqali mantiqiy tahlil
        $prompt = "Talaba arizasini tahlil qil va uning salohiyatini bahola. Ismi: {$app->name}, Yo'nalish: {$app->direction}, Daraja: {$app->level}. Uning uchun kichik mantiqiy test savoli tayyorla va ruxsat berish haqida tavsiya ber. Javobni JSON formatida qaytar: {assessment: string, status: 'accepted'|'rejected', logic_test: string}";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            $aiResult = $response->json()['candidates'][0]['content']['parts'][0]['text'];
            // Tozalash va saqlash
            $cleanedJson = preg_replace('/```json|```/', '', $aiResult);
            
            DB::table('academy_applications')->where('id', $appId)->update([
                'ai_assessment' => $cleanedJson,
                'status' => 'test_sent'
            ]);

            // Email yuborish (Muvaffaqiyatli qabul)
            Mail::to($app->email)->send(new \App\Mail\AcademyWelcome($app));
            
        } catch (\Exception $e) {
            \Log::error("Academy AI Error: " . $e->getMessage());
        }
    }

    // Arizalar ro'yxati (Master Panel uchun)
    public function getApplications()
    {
        $apps = DB::table('academy_applications')
                    ->orderBy('created_at', 'desc')
                    ->get();
        return response()->json($apps);
    }

    // Arizani tahrirlash
    public function updateApplication(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|string'
        ]);

        $updateData = $request->only(['name', 'email', 'phone', 'location', 'direction', 'level', 'status']);
        $updateData['updated_at'] = now();

        DB::table('academy_applications')->where('id', $id)->update(array_filter($updateData));

        return response()->json(['status' => 'success', 'message' => 'Ariza muvaffaqiyatli yangilandi.']);
    }

    // Arizani rad etish
    public function rejectApplication($id)
    {
        DB::table('academy_applications')->where('id', $id)->update(['status' => 'rejected']);
        return response()->json(['status' => 'success', 'message' => 'Ariza rad etildi.']);
    }

    // Arizani o'chirish
    public function deleteApplication($id)
    {
        DB::table('academy_applications')->where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Ariza o\'chirildi.']);
    }

    // Arizani qabul qilish va o'quvchiga aylantirish
    public function approveApplication($id)
    {
        $app = DB::table('academy_applications')->where('id', $id)->first();
        if (!$app) return response()->json(['status' => 'error', 'message' => 'Ariza topilmadi.'], 404);

        // 1. User yaratish yoki yangilash
        $user = \App\Models\User::updateOrCreate(
            ['email' => $app->email],
            [
                'name' => $app->name,
                'password' => \Illuminate\Support\Facades\Hash::make('academy2026'), // Defolt parol
                'role' => 'student',
                'phone' => $app->phone
            ]
        );

        // 2. Progress yaratish
        DB::table('academy_progress')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'total_xp' => 10, // Bonus start XP
                'level' => 'Junior',
                'direction' => $app->direction,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 3. Ariza statusini o'zgartirish
        DB::table('academy_applications')->where('id', $id)->update(['status' => 'accepted']);

        return response()->json(['status' => 'success', 'message' => "{$app->name} muvaffaqiyatli o'quvchi sifatida qabul qilindi!"]);
    }

    public function getStats()
    {
        return response()->json([
            'total_students' => DB::table('academy_progress')->count(),
            'pending_apps' => DB::table('academy_applications')->where('status', 'pending')->count(),
            'active_tasks' => DB::table('academy_tasks')->count(),
            'top_students' => DB::table('academy_progress')
                                ->join('users', 'academy_progress.user_id', '=', 'users.id')
                                ->select('users.name', 'academy_progress.total_xp')
                                ->orderBy('total_xp', 'desc')
                                ->limit(5)
                                ->get()
        ]);
    }

    // I-Ticher: Avtomatik dars generatsiyasi
    public function generateLesson(Request $request)
    {
        $user = auth()->user();
        $student = DB::table('academy_progress')
                    ->where('user_id', $user->id)
                    ->first();

        // Agar o'quvchi bo'lmasa, master panel so'rovidir
        if (!$student && $request->student_id) {
            $student = DB::table('academy_progress')
                        ->join('users', 'academy_progress.user_id', '=', 'users.id')
                        ->where('users.id', $request->student_id)
                        ->select('users.name', 'academy_progress.*')
                        ->first();
        }

        if (!$student) return response()->json(['status' => 'error', 'message' => 'Talaba topilmadi.']);

        $prompt = "Sen 'I-Ticher' AI o'qituvchisisan. Talaba ismi: {$student->name}. Uning joriy XP: {$student->total_xp}. Unga navbatdagi dars mavzusi, tushuntirish va 3 ta savolli test tayyorla. Javobni chiroyli Markdown formatida qaytar.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);
            $lessonContent = $response->json()['candidates'][0]['content']['parts'][0]['text'];
            
            return response()->json(['status' => 'success', 'lesson' => $lessonContent]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'I-Ticher hozir band.']);
        }
    }

    // Sandboxga kod yuborish
    public function submitSandbox(Request $request)
    {
        $request->validate(['code' => 'required', 'project_name' => 'required']);
        $user = auth()->user();

        // AI Security Check
        $prompt = "Ushbu kodni havsizlik bo'yicha tahlil qil. Agar zararli bo'lsa 'blocked', shubhali bo'lsa 'risky', havfsiz bo'lsa 'safe' deb javob ber. Faqat bitta so'z yoz. Kod: " . $request->code;
        
        $aiRes = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiKey}", [
            'contents' => [['parts' => [['text' => $prompt]]]]
        ])->json();

        $status = strtolower(trim($aiRes['candidates'][0]['content']['parts'][0]['text'] ?? 'safe'));
        if (!in_array($status, ['safe', 'risky', 'blocked'])) $status = 'safe';

        $id = DB::table('academy_sandboxes')->insertGetId([
            'user_id' => $user->id,
            'project_name' => $request->project_name,
            'submitted_code' => $request->code,
            'ai_security_status' => $status,
            'created_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'security_status' => $status,
            'message' => $status === 'blocked' ? 'Kodda havfli elementlar bor!' : 'Kod Sandboxga saqlandi.'
        ]);
    }

    // Bounty (Vazifa) topshirish
    public function submitTask(Request $request)
    {
        $request->validate(['task_id' => 'required', 'submission' => 'required']);
        $user = auth()->user();
        $task = DB::table('academy_tasks')->where('id', $request->task_id)->first();

        // Progressni yangilash
        DB::table('academy_progress')->where('user_id', $user->id)->increment('total_xp', $task->xp_reward);
        if ($task->bounty_reward > 0) {
            DB::table('academy_progress')->where('user_id', $user->id)->increment('earned_bounty', $task->bounty_reward);
        }

        return response()->json(['status' => 'success', 'message' => "Vazifa topshirildi. Mukofotlar hisobingizga o'tdi!"]);
    }
}
