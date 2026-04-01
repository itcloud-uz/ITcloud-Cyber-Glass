<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademyProSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Achievements (Gamification)
        $achievements = [
            ['name' => 'Fast Learner', 'description' => 'Bitirgan birinchi dars uchun', 'icon' => 'fa-bolt', 'points' => 100],
            ['name' => 'Code Warrior', 'description' => '10 ta vazifani muvaffaqiyatli topshirganlar uchun', 'icon' => 'fa-shield-halved', 'points' => 500],
            ['name' => 'Master of Logic', 'description' => 'AI tahlilida 90+ ball to\'plaganlar uchun', 'icon' => 'fa-brain', 'points' => 1000],
            ['name' => 'Legacy Hunter', 'description' => '1-oylik streakni saqlab qolganlar uchun', 'icon' => 'fa-fire', 'points' => 2000],
        ];
        foreach($achievements as $a) DB::table('academy_achievements')->updateOrInsert(['name' => $a['name']], $a);

        // 2. Jobs (Career Center)
        $jobs = [
            [
                'company_name' => 'ITcloud Tech',
                'title' => 'Junior Frontend Developer',
                'description' => 'React va Next.js bo\'yicha boshlang\'ich bilimga ega dasturchilarni ishga olamiz.',
                'location' => 'Toshkent / Masofaviy',
                'salary_range_min' => 4000000,
                'salary_range_max' => 8000000,
                'created_at' => now()
            ],
            [
                'company_name' => 'CyberGlass Systems',
                'title' => 'AI Research Intern',
                'description' => 'LLMlar bilan ishlash tajribasiga ega o\'quvchilarni stajirovkaga taklif qilamiz.',
                'location' => 'Gibrid',
                'salary_range_min' => 3000000,
                'salary_range_max' => 6000000,
                'created_at' => now()
            ]
        ];
        foreach($jobs as $j) DB::table('academy_jobs')->insert($j);

        // 3. Mentors & Courses (Existing structure updated)
        $mentors = [
            [
                'name' => 'I-Ticher Frontend Mastery',
                'system_prompt' => "Sen 10 yillik tajribaga ega Frontend Architect san. Vazifang: O'quvchilarga React, Next.js, CSS arxitekturasi va UI/UX qoidalarini o'rgatish. Dars tushuntiring, kodlarni tahlil qiling va xatolarni tushuntirib bering.",
                'gemini_api_key' => env('GEMINI_API_KEY'),
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'I-Ticher Backend Guru',
                'system_prompt' => "Sen 10 yillik tajribaga ega Backend Architect san. Vazifang: PHP/Laravel, Node.js, API design, Redis, Postgres va Microservices haqida chuqur bilim berish. Tizim xavfsizligi va arxitektura bo'yicha maslahat berasiz.",
                'gemini_api_key' => env('GEMINI_API_KEY'),
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'I-Ticher AI Engineer',
                'system_prompt' => "Sen AI Agentlar va LLM mutaxassisisan. Vazifang: Gemini API, LangChain, embeddings va NLP texnologiyalarini qo'llashni o'rgatish. O'quvchiga o'z agentlarini yaratishda yo'l ko'rsatasiz.",
                'gemini_api_key' => env('GEMINI_API_KEY'),
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
        ];

        foreach ($mentors as $m) {
            $mentor = DB::table('academy_mentors')->where('name', $m['name'])->first();
            if (!$mentor) {
                $mentor_id = DB::table('academy_mentors')->insertGetId($m);
            } else {
                $mentor_id = $mentor->id;
            }
            
            // Courses
            $course_title = str_replace('I-Ticher ', '', $m['name']);
            if (!DB::table('academy_courses')->where('title', 'like', "%$course_title%")->exists()) {
                DB::table('academy_courses')->insert([
                    'title' => $course_title,
                    'description' => "Professional kurs: " . $m['name'],
                    'mentor_id' => $mentor_id,
                    'price' => 3000000,
                    'monthly_fee' => 800000,
                    'is_published' => true,
                    'created_at' => now()
                ]);
            }
        }
    }
}
