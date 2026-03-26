<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. AI Mentors table
        Schema::create('academy_mentors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('instructions')->nullable(); // Gemini System Prompt
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Enhance Courses
        Schema::table('academy_courses', function (Blueprint $table) {
            $table->foreignId('mentor_id')->nullable()->constrained('academy_mentors')->nullOnDelete();
            $table->boolean('is_published')->default(false);
        });

        // 3. Lessons table
        Schema::create('academy_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('academy_courses')->cascadeOnDelete();
            $table->string('title');
            $table->json('content')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 4. Enhance Progress (Talents & Ratings)
        Schema::table('academy_progress', function (Blueprint $table) {
            $table->json('talents')->nullable(); // {iq: 120, eq: 110, tech: 85, soft: 90}
            $table->json('grades')->nullable();  // Cache of results
            $table->string('rank')->default('Junior');
            $table->string('status')->default('enrolled');
        });

        // 5. Grades/Results table
        Schema::create('academy_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('academy_lessons')->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->text('ai_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_results');
        Schema::dropIfExists('academy_lessons');
        Schema::table('academy_progress', function (Blueprint $table) {
            $table->dropColumn(['talents', 'grades', 'rank', 'status']);
        });
        Schema::table('academy_courses', function (Blueprint $table) {
            $table->dropColumn(['mentor_id', 'is_published']);
        });
        Schema::dropIfExists('academy_mentors');
    }
};
