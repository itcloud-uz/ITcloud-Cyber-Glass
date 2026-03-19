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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
            $table->string('passport_number')->nullable();
            $table->string('face_id_photo_path')->nullable(); // Face ID Base64 yoki rasm manzili
            $table->boolean('is_face_id_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'passport_number', 'face_id_photo_path', 'is_face_id_enabled']);
        });
    }
};
