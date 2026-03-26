<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_global_chat', function (Blueprint $table) {
            $table->unsignedBigInteger('receiver_id')->nullable()->after('user_id');
            $table->string('receiver_type')->default('user')->after('receiver_id'); // user, mentor
        });
    }

    public function down(): void
    {
        Schema::table('academy_global_chat', function (Blueprint $table) {
            $table->dropColumn(['receiver_id', 'receiver_type']);
        });
    }
};
