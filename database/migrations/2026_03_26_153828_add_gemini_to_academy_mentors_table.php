<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_mentors', function (Blueprint $table) {
            $table->string('gemini_api_key')->nullable()->after('name');
            $table->text('system_prompt')->nullable()->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('academy_mentors', function (Blueprint $table) {
            $table->dropColumn(['gemini_api_key', 'system_prompt']);
        });
    }
};
