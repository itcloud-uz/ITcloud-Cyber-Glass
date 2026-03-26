<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Student Projects Table
        Schema::create('academy_student_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('status')->default('draft'); // draft, in_progress, finished, testing
            $table->integer('test_score')->nullable();
            $table->json('tech_stack')->nullable();
            $table->timestamps();
        });

        // Global Academy Chat Table
        Schema::create('academy_global_chat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('ai_status')->default('clean'); // clean, flagged, deleted
            $table->string('ai_moderator_note')->nullable();
            $table->timestamps();
        });

        // Moderation Log/Punishments Table
        Schema::create('academy_moderations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('violation_type'); // insult, spam, inappropriate_content
            $table->string('punishment'); // blocked, task_penalty, expelled
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_moderations');
        Schema::dropIfExists('academy_global_chat');
        Schema::dropIfExists('academy_student_projects');
    }
};
