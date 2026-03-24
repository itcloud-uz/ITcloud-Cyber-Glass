<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Talabgorlar (Ariza topshirganlar)
        Schema::create('academy_applications', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('email')->unique();
            $blueprint->string('phone');
            $blueprint->string('location')->nullable();
            $blueprint->string('direction'); // Frontend, Backend, AI Engineer
            $blueprint->string('level'); // Beginner, Intermediate
            $blueprint->json('ai_assessment')->nullable(); // AI tahlili (mantiq, qobiliyat)
            $blueprint->enum('status', ['pending', 'accepted', 'rejected', 'test_sent'])->default('pending');
            $blueprint->string('access_token')->nullable(); // Maxfiy login uchun
            $blueprint->timestamps();
        });

        // Kurslar
        Schema::create('academy_courses', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->text('description');
            $blueprint->string('video_url')->nullable();
            $blueprint->integer('order')->default(0);
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });

        // Vazifalar va Bounty tizimi
        Schema::create('academy_tasks', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->text('description');
            $blueprint->integer('xp_reward')->default(50);
            $blueprint->decimal('bounty_reward', 10, 2)->default(0); // Haqiqiy pul mukofoti (UZS)
            $blueprint->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            $blueprint->timestamps();
        });

        // Talaba progressi
        Schema::create('academy_progress', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->integer('total_xp')->default(0);
            $blueprint->json('completed_lessons')->nullable();
            $blueprint->decimal('earned_bounty', 10, 2)->default(0);
            $blueprint->timestamps();
        });

        // Sandbox (Xavfsiz hudud)
        Schema::create('academy_sandboxes', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained();
            $blueprint->string('project_name');
            $blueprint->text('submitted_code')->nullable();
            $blueprint->enum('ai_security_status', ['safe', 'risky', 'blocked'])->default('safe');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_sandboxes');
        Schema::dropIfExists('academy_progress');
        Schema::dropIfExists('academy_tasks');
        Schema::dropIfExists('academy_courses');
        Schema::dropIfExists('academy_applications');
    }
};
