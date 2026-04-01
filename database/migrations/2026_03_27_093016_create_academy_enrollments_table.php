<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create enrollments table
        if (!Schema::hasTable('academy_enrollments')) {
            Schema::create('academy_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('academy_courses')->cascadeOnDelete();
                $table->enum('status', ['active', 'completed', 'retake', 'dropped', 'extended'])->default('active');
                $table->date('enrolled_at')->nullable();
                $table->date('expires_at')->nullable();
                $table->integer('progress_percent')->default(0);
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add enrollment_id to academy_progress to track "current" active course if needed
        // (Optional, results table already tracks progress)
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_enrollments');
    }
};
