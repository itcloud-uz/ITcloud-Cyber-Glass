<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Achievements (Gamification)
        Schema::create('academy_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('icon')->default('fa-medal');
            $table->integer('points')->default(100);
            $table->timestamps();
        });

        Schema::create('academy_user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('academy_achievements')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Certificates
        Schema::create('academy_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained('academy_courses')->onDelete('cascade');
            $table->string('certificate_no')->unique();
            $table->string('file_path')->nullable();
            $table->string('verify_token')->unique();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });

        // 3. Jobs (Career Center)
        Schema::create('academy_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->decimal('salary_range_min', 15, 2)->nullable();
            $table->decimal('salary_range_max', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academy_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('academy_jobs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['applied', 'interview', 'hired', 'rejected'])->default('applied');
            $table->text('ai_resume_summary')->nullable();
            $table->timestamps();
        });

        // 4. Progress Enhancements
        Schema::table('academy_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('academy_progress', 'streak_count')) {
                $table->integer('streak_count')->default(0);
            }
            if (!Schema::hasColumn('academy_progress', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable();
            }
            if (!Schema::hasColumn('academy_progress', 'is_career_ready')) {
                $table->boolean('is_career_ready')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('academy_progress', function (Blueprint $table) {
            $table->dropColumn(['streak_count', 'last_activity_at', 'is_career_ready']);
        });
        Schema::dropIfExists('academy_job_applications');
        Schema::dropIfExists('academy_jobs');
        Schema::dropIfExists('academy_certificates');
        Schema::dropIfExists('academy_user_achievements');
        Schema::dropIfExists('academy_achievements');
    }
};
