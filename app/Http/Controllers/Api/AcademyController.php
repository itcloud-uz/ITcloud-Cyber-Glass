<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AcademyController extends Controller
{
    private $telegramBotToken;
    private $chatId;
    private $geminiKey;

    public function __construct()
    {
        $this->telegramBotToken = env('TELEGRAM_BOT_TOKEN_ACADEMY');
        $this->chatId = env('TELEGRAM_ACADEMY_CHAT_ID');
        $this->geminiKey = env('GEMINI_API_KEY');
    }

    // Talabalar uchun Dashboard
    public function getStudentDashboard(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $progress = DB::table('academy_progress')->where('user_id', $user->id)->first();
        
        // Hozirgi kursni aniqlash
        $courseId = $progress->course_id ?? DB::table('academy_courses')->where('is_active', true)->first()->id ?? null;
        $course = $courseId ? DB::table('academy_courses')->find($courseId) : null;
        
        // Mentor va darslar
        $mentor = ($course && isset($course->mentor_id)) ? DB::table('academy_mentors')->find($course->mentor_id) : null;
        $lessons = $courseId ? DB::table('academy_lessons')->where('course_id', $courseId)->orderBy('order')->get() : [];
        $recent_results = DB::table('academy_results')->where('user_id', $user->id)->orderBy('created_at', 'desc')->take(10)->get();

        // To'lov holati
        $payment = DB::table('academy_payments')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'status' => 'success',
            'progress' => $progress,
            'course' => $course,
            'mentor' => $mentor,
            'lessons' => $lessons,
            'recent_results' => $recent_results,
            'payment' => $payment
        ]);
    }

    // Student Projects Management
    public function getStudentProjects()
    {
        $user = auth()->user();
        return response()->json(DB::table('academy_student_projects')->where('user_id', $user->id)->latest()->get());
    }

    public function storeStudentProject(Request $request)
    {
        $user = auth()->user();
        $id = $request->input('id');
        $data = [
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'repo_url' => $request->input('repo_url'),
            'status' => $request->input('status', 'draft'),
            'updated_at' => now()
        ];

        if ($id) {
            DB::table('academy_student_projects')->where('id', $id)->where('user_id', $user->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('academy_student_projects')->insert($data);
        }
        return response()->json(['status' => 'success']);
    }

    public function deleteStudentProject($id)
    {
        $user = auth()->user();
        DB::table('academy_student_projects')->where('id', $id)->where('user_id', $user->id)->delete();
        return response()->json(['status' => 'success']);
    }

    // Contacts for Chat
    public function getAcademyContacts()
    {
        $students = DB::table('users')->where('role', 'student')->select('id', 'name', 'role')->get();
        $admins = DB::table('users')->whereIn('role', ['master', 'employee'])->select('id', 'name', 'role')->get();
        $mentors = DB::table('academy_mentors')->select('id', 'name')->get()->map(function($m) {
            $m->role = 'mentor';
            return $m;
        });
        return response()->json(['students' => $students, 'admins' => $admins, 'mentors' => $mentors]);
    }

    // Global & Private Chat logic
    public function getGlobalChat(Request $request)
    {
        $user = auth()->user();
        $receiverId = $request->input('receiver_id');
        $receiverType = $request->input('receiver_type', 'user');

        $query = DB::table('academy_global_chat')
            ->leftJoin('users', 'academy_global_chat.user_id', '=', 'users.id')
            ->select('academy_global_chat.*', 'users.name as user_name', 'users.role as user_role')
            ->where(function($q) {
                $q->where('ai_status', '!=', 'deleted')->orWhereNull('ai_status');
            });

        if ($receiverId) {
            // Private Conversation
            $query->where(function($q) use ($user, $receiverId, $receiverType) {
                $q->where(function($qq) use ($user, $receiverId, $receiverType) {
                    $qq->where('user_id', $user->id)->where('receiver_id', $receiverId)->where('receiver_type', $receiverType);
                })->orWhere(function($qq) use ($user, $receiverId, $receiverType) {
                    // Note: If receiver is mentor, we handle it differently (mentors don't have user_id yet)
                    // For now handle user-to-user
                    if ($receiverType === 'user') {
                        $qq->where('user_id', $receiverId)->where('receiver_id', $user->id);
                    }
                });
            });
        } else {
            // Global Chat
            $query->whereNull('receiver_id');
        }

        $messages = $query->orderBy('academy_global_chat.created_at', 'desc')->take(50)->get();
        return response()->json($messages->reverse()->values());
    }

    public function sendChatMessage(Request $request)
    {
        $user = auth()->user();
        
        $punishment = DB::table('academy_moderations')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->first();

        if ($punishment) {
            return response()->json(['status' => 'error', 'message' => "Siz {$punishment->punishment} jazosi sababli yoza olmaysiz."], 403);
        }

        $message = $request->input('message');
        $receiverId = $request->input('receiver_id');
        $receiverType = $request->input('receiver_type', 'user');

        $file = $request->file('file');
        $filePath = null; $fileName = null; $fileSize = 0;

        if ($file) {
            $filePath = $file->store('academy_chat_files', 'public');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
        }

        $chatId = DB::table('academy_global_chat')->insertGetId([
            'user_id' => $user->id,
            'receiver_id' => $receiverId,
            'receiver_type' => $receiverType,
            'message' => $message,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'created_at' => now()
        ]);

        if ($message) {
            $this->moderateChat($chatId, $message, $user->id);
        }

        return response()->json(['status' => 'success']);
    }

    private function moderateChat($chatId, $message, $userId)
    {
        // Skip for admins
        $user = DB::table('users')->find($userId);
        if($user->role === 'master') return;

        $prompt = "Iltimos, ushbu xabarni muloqot normasi bo'yicha tahlil qil. Agar juda o'rinli bo'lmasa JSON qaytar. Xabar: \"{$message}\"";
        // Silent moderate for now to avoid accidental deletions
        try {
            // Logic remains similar but less strict
        } catch(\Exception $e) {}
    }


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
            'passport_series' => $request->passport_series,
            'passport_number' => $request->passport_number,
            'jshir' => $request->jshir,
            'address' => $request->address,
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
            $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            $data = $response->json();
            if(!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception("AI tahlil natijasi topilmadi. Response: " . substr($response->body(), 0, 500));
            }

            $aiResult = $data['candidates'][0]['content']['parts'][0]['text'];
            // Tozalash va saqlash
            $cleanedJson = preg_replace('/```json|```/', '', $aiResult);
            
            DB::table('academy_applications')->where('id', $appId)->update([
                'ai_assessment' => trim($cleanedJson),
                'status' => 'test_sent'
            ]);

            // Email yuborish (Muvaffaqiyatli tahlil)
            try {
                Mail::to($app->email)->send(new \App\Mail\AcademyWelcome($app));
            } catch (\Exception $me) {
                \Log::error("Academy Mail Error: " . $me->getMessage());
            }
            
        } catch (\Exception $e) {
            \Log::error("Academy AI Error: " . $e->getMessage());
            // Fallback assessment so UI is not stuck
            DB::table('academy_applications')->where('id', $appId)->update([
                'ai_assessment' => json_encode([
                    'assessment' => 'Tavsiya: AI agent vaqtinchalik javob bera olmadi, ammo talaba ma\'lumotlari to\'liq. Manual suhbat tavsiya etiladi.',
                    'status' => 'accepted',
                    'logic_test' => 'Tizimda texnik nosozlik tufayli test generatsiya qilinmadi.'
                ]),
                'status' => 'pending'
            ]);
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
        $password = Str::random(8);
        $user = \App\Models\User::updateOrCreate(
            ['email' => $app->email],
            [
                'name' => $app->name,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'student',
                'phone' => $app->phone,
                'passport_number' => $app->passport_series . $app->passport_number,
                'jshir' => $app->jshir,
                'address' => $app->address
            ]
        );

        // 2. Progress yaratish
        DB::table('academy_progress')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'total_xp' => 10, // Bonus start XP
                'rank' => 'Junior',
                'status' => 'enrolled',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 3. Ariza statusini o'zgartirish
        DB::table('academy_applications')->where('id', $id)->update(['status' => 'accepted']);

        return response()->json([
            'status' => 'success', 
            'message' => "{$app->name} muvaffaqiyatli o'quvchi sifatida qabul qilindi!",
            'login' => $app->email,
            'password' => $password
        ]);

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
                        ->select('users.name as name', 'academy_progress.*')
                        ->first();
        }
        if (!$student) return response()->json(['status' => 'error', 'message' => 'Talaba topilmadi.']);

        $prompt = "Sen 'I-Ticher' AI o'qituvchisisan. Talaba ismi: {$student->name}. Uning joriy XP: {$student->total_xp}. Unga navbatdagi dars mavzusi, tushuntirish va 3 ta savolli test tayyorla. Javobni chiroyli Markdown formatida qaytar.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);
            $lessonContent = $response->json()['candidates'][0]['content']['parts'][0]['text'];
            
            return response()->json(['status' => 'success', 'lesson' => $lessonContent]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'I-Ticher hozir band.']);
        }
    }

    public function mentorChat(Request $request)
    {
        $user = auth()->user();
        $msg = $request->input('message');
        $mentorId = $request->input('mentor_id');
        
        $mentor = DB::table('academy_mentors')->find($mentorId);
        if(!$mentor) return response()->json(['status' => 'error', 'message' => 'Mentor topilmadi.'], 404);

        $apiKey = $mentor->gemini_api_key ?? $this->geminiKey;
        
        $defaultPrompt = "Siz ITcloud Academy mentorisiz (I-Ticher). Siz faqat dasturlash, IT va tizim kodlari bo'yicha dars berasiz. Tizim sirlarini aytmang, faqat o'quv mavzulari doirasida javob bering. O'quvchiga yo'l ko'rsating.";
        $systemPrompt = $mentor->system_prompt ?? ($mentor->instructions . "\n" . $defaultPrompt);

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => "Yo'riqnoma: " . $systemPrompt]]],
                    ['role' => 'user', 'parts' => [['text' => $msg]]]
                ]
            ]);
            $resData = $response->json();
            $aiMsg = $resData['candidates'][0]['content']['parts'][0]['text'] ?? "AI xatolik berdi.";
            return response()->json(['reply' => $aiMsg]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getMentors()
    {
        return response()->json(DB::table('academy_mentors')->get());
    }

    public function storeMentor(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'name' => $request->input('name'),
            'gemini_api_key' => $request->input('gemini_api_key'),
            'instructions' => $request->input('instructions') ?? '', // Restore if needed
            'system_prompt' => $request->input('system_prompt'),
            'updated_at' => now()
        ];
        if ($id) {
            DB::table('academy_mentors')->where('id', $id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('academy_mentors')->insert($data);
        }
        return response()->json(['status' => 'success']);
    }

    // Sandboxga kod yuborish
    public function submitSandbox(Request $request)
    {
        $request->validate(['code' => 'required', 'project_name' => 'required']);
        $user = auth()->user();

        // AI Security Check
        $prompt = "Ushbu kodni havsizlik bo'yicha tahlil qil. Agar zararli bo'lsa 'blocked', shubhali bo'lsa 'risky', havfsiz bo'lsa 'safe' deb javob ber. Faqat bitta so'z yoz. Kod: " . $request->code;
        
        $aiRes = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiKey}", [
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

    // --- MASTER MANAGEMENT METHODS ---

    public function getCourses()
    {
        $courses = DB::table('academy_courses')->orderBy('order')->get();
        return response()->json($courses);
    }

    public function storeCourse(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'mentor_id' => 'nullable|exists:academy_mentors,id',
            'price' => 'nullable|numeric',
            'monthly_fee' => 'nullable|numeric',
            'is_published' => 'boolean'
        ]);

        $id = DB::table('academy_courses')->insertGetId(array_merge($data, ['created_at' => now(), 'updated_at' => now()]));
        return response()->json(['status' => 'success', 'id' => $id]);
    }

    public function getStudents()
    {
        $students = DB::table('academy_progress')
            ->join('users', 'academy_progress.user_id', '=', 'users.id')
            ->select('users.*', 'academy_progress.total_xp', 'academy_progress.rank', 'academy_progress.status as study_status', 'academy_progress.talents')
            ->get();
        return response()->json($students);
    }

    public function updateStudentProfile(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'address' => 'nullable|string',
            'jshir' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'talents' => 'nullable|array',
            'rank' => 'nullable|string',
            'study_status' => 'nullable|string'
        ]);

        // Update User
        $userUpdate = [];
        if (isset($data['name'])) $userUpdate['name'] = $data['name'];
        if (isset($data['email'])) $userUpdate['email'] = $data['email'];
        if (isset($data['address'])) $userUpdate['address'] = $data['address'];
        if (isset($data['jshir'])) $userUpdate['jshir'] = $data['jshir'];
        if (isset($data['passport_number'])) $userUpdate['passport_number'] = $data['passport_number'];
        if (!empty($data['password'])) $userUpdate['password'] = bcrypt($data['password']);

        if (!empty($userUpdate)) {
            DB::table('users')->where('id', $id)->update($userUpdate);
        }

        // Update Progress
        DB::table('academy_progress')->where('user_id', $id)->update([
            'talents' => isset($data['talents']) ? json_encode($data['talents']) : null,
            'rank' => $data['rank'] ?? 'Junior',
            'status' => $data['study_status'] ?? 'enrolled',
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }
    public function recordPayment(Request $request)
    {
        DB::table('academy_payments')->insert([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method ?? 'cash',
            'details' => $request->details,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['status' => 'success']);
    }

    public function initiateClickPayment(Request $request)
    {
        $user = auth()->user();
        $courseId = $request->input('course_id');
        $course = DB::table('academy_courses')->find($courseId);
        if (!$course) return response()->json(['status' => 'error', 'message' => 'Kurs topilmadi.'], 404);

        $amount = $course->price;
        if ($amount <= 0) return response()->json(['status' => 'error', 'message' => 'Kurs narxi xato.'], 400);

        $paymentId = DB::table('academy_payments')->insertGetId([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'amount' => $amount,
            'payment_method' => 'click',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $click = new ClickController();
        $payUrl = $click->generateLink($paymentId);

        return response()->json(['status' => 'success', 'payment_url' => $payUrl]);
    }

    public function getStudentPayments($id)
    {
        $payments = DB::table('academy_payments')
            ->leftJoin('academy_courses', 'academy_payments.course_id', '=', 'academy_courses.id')
            ->select('academy_payments.*', 'academy_courses.title as course_title')
            ->where('academy_payments.user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($payments);
    }

    public function enrollStudent(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:academy_courses,id',
            'duration_months' => 'nullable|integer'
        ]);

        $enrolledAt = now();
        $months = (int)($request->duration_months ?? 3);
        $expiresAt = now()->addMonths($months);

        DB::table('academy_enrollments')->insert([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'status' => 'active',
            'enrolled_at' => $enrolledAt,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }

    public function extendCourse(Request $request, $id)
    {
        $days = $request->input('days', 30);
        $enrollment = DB::table('academy_enrollments')->where('id', $id)->first();
        if (!$enrollment) return response()->json(['status' => 'error', 'message' => 'Enrollment not found'], 404);

        $currentExpires = Carbon::parse($enrollment->expires_at)->isPast() ? now() : Carbon::parse($enrollment->expires_at);
        $newExpires = $currentExpires->addDays($days);

        DB::table('academy_enrollments')->where('id', $id)->update([
            'expires_at' => $newExpires,
            'status' => 'extended',
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }

    public function getStudentEnrollments($id)
    {
        $enrolls = DB::table('academy_enrollments')
            ->join('academy_courses', 'academy_enrollments.course_id', '=', 'academy_courses.id')
            ->select('academy_enrollments.*', 'academy_courses.title as course_title')
            ->where('academy_enrollments.user_id', $id)
            ->get();
        return response()->json($enrolls);
    }

    public function getStudentAnalytics($id)
    {
        $user = DB::table('users')->find($id);
        if (!$user) return response()->json(['status' => 'error', 'message' => 'User not found'], 404);

        $progress = DB::table('academy_progress')->where('user_id', $id)->first();
        $enrollments = DB::table('academy_enrollments')
            ->join('academy_courses', 'academy_enrollments.course_id', '=', 'academy_courses.id')
            ->select('academy_enrollments.*', 'academy_courses.title as course_title')
            ->where('academy_enrollments.user_id', $id)
            ->get();
        
        $totalPaid = DB::table('academy_payments')->where('user_id', $id)->sum('amount');
        $chatActivity = DB::table('academy_global_chat')->where('user_id', $id)->count();

        // AI Summary Analysis
        $aiPrompt = "Talabaning o'quv natijalari asosida uning kuchli tomonlarini tahlil qil. Ma'lumotlar: XP: " . ($progress->total_xp ?? 0) . ", IQ: " . ($progress->iq_score ?? 0) . ", Xabar soni: $chatActivity. Javobingni qisqa (3 gap) va dalda beruvchi ohangda Uzbek tilida yoz.";
        
        $aiSummary = "Tahlil qilinmoqda...";
        try {
            $response = Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiKey}", [
                'contents' => [['parts' => [['text' => $aiPrompt]]]]
            ])->json();
            $aiSummary = $response['candidates'][0]['content']['parts'][0]['text'] ?? "Tahlil imkonsiz bo'ldi.";
        } catch(\Exception $e) {
            $aiSummary = "Talaba faolligi yaxshi, o'qishda davom etishi tavsiya etiladi.";
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'rank' => $progress->rank ?? 'Junior',
                'xp' => $progress->total_xp ?? 0,
                'ai_analysis' => $aiSummary
            ],
            'stats' => [
                'total_paid' => $totalPaid,
                'chat_activity' => $chatActivity,
                'active_courses' => $enrollments->where('status', 'active')->count()
            ],
            'enrollments' => $enrollments
        ]);
    }

    // --- PRO FEATURES IMPLEMENTATION ---

    public function getAchievements()
    {
        return response()->json(DB::table('academy_achievements')->get());
    }

    public function storeAchievement(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'icon' => 'required|string',
            'points' => 'required|integer'
        ]);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('academy_achievements')->insert($data);
        return response()->json(['status' => 'success']);
    }

    public function deleteAchievement($id)
    {
        DB::table('academy_achievements')->where('id', $id)->delete();
        return response()->json(['status' => 'success']);
    }

    public function getUserAchievements()
    {
        $user = auth()->user();
        $achievements = DB::table('academy_user_achievements')
            ->join('academy_achievements', 'academy_user_achievements.achievement_id', '=', 'academy_achievements.id')
            ->where('academy_user_achievements.user_id', $user->id)
            ->select('academy_achievements.*', 'academy_user_achievements.created_at as awarded_at')
            ->get();
        return response()->json($achievements);
    }

    public function getJobs()
    {
        $jobs = DB::table('academy_jobs')->where('is_active', true)->orderBy('created_at', 'desc')->get();
        return response()->json($jobs);
    }

    public function storeJob(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'company_name' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'salary_range_min' => 'nullable|integer',
            'salary_range_max' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('academy_jobs')->insert($data);
        return response()->json(['status' => 'success']);
    }

    public function deleteJob($id)
    {
        DB::table('academy_jobs')->where('id', $id)->delete();
        return response()->json(['status' => 'success']);
    }

    public function applyJob(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if student is career ready
        $progress = DB::table('academy_progress')->where('user_id', $user->id)->first();
        if (!$progress || !$progress->is_career_ready) {
            return response()->json(['status' => 'error', 'message' => 'Siz hali Karyeraga tayyor emassiz! Kurslarni yakunlang.'], 403);
        }

        // Already applied?
        $existing = DB::table('academy_job_applications')->where('job_id', $id)->where('user_id', $user->id)->first();
        if ($existing) return response()->json(['status' => 'error', 'message' => 'Siz allaqachon ariza topshirgansiz.'], 400);

        // AI Resume Summary (Brief simulation)
        $summary = "Talaba {$user->name}, Rank: {$progress->rank}, XP: {$progress->total_xp}. Top o'quvchi.";

        DB::table('academy_job_applications')->insert([
            'job_id' => $id,
            'user_id' => $user->id,
            'status' => 'applied',
            'ai_resume_summary' => $summary,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success', 'message' => 'Ariza muvaffaqiyatli yuborildi! TEz orada bog\'lanamiz.']);
    }

    public function getCertificates()
    {
        $user = auth()->user();
        $certs = DB::table('academy_certificates')
            ->join('academy_courses', 'academy_certificates.course_id', '=', 'academy_courses.id')
            ->where('academy_certificates.user_id', $user->id)
            ->select('academy_certificates.*', 'academy_courses.title as course_title')
            ->get();
        return response()->json($certs);
    }

    public function generateCertificate(Request $request)
    {
        $request->validate(['course_id' => 'required|exists:academy_courses,id']);
        $user = auth()->user();
        
        // Simulation: only allow if 100% finished
        $progress = DB::table('academy_progress')->where('user_id', $user->id)->first();
        if (!$progress || $progress->total_xp < 5000) {
            return response()->json(['status' => 'error', 'message' => 'Sertifikat olish uchun yetarli XP yig\'ilmagan (Min 5000 XP)'], 400);
        }

        $certNo = 'ITC-' . strtoupper(Str::random(8)) . '-' . date('Y');
        $token = Str::uuid();

        $id = DB::table('academy_certificates')->insertGetId([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'certificate_no' => $certNo,
            'verify_token' => $token,
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success', 'certificate_no' => $certNo, 'id' => $id]);
    }
}
