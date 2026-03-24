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
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('operator')->after('email');
            }
            if (!Schema::hasColumn('users', 'passport_number')) {
                $table->string('passport_number')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'face_id_photo_path')) {
                $table->string('face_id_photo_path')->nullable()->after('passport_number');
            }
            if (!Schema::hasColumn('users', 'is_face_id_enabled')) {
                $table->boolean('is_face_id_enabled')->default(false)->after('face_id_photo_path');
            }
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
