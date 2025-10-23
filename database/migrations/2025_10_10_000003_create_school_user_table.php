<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role'); // 'teacher' | 'supervisor'
            $table->timestamps();

            $table->unique(['school_id', 'user_id', 'role']);
        });

        // Partial unique index to ensure a user can be a TEACHER in at most one school (PostgreSQL)
        DB::statement("CREATE UNIQUE INDEX school_user_teacher_unique ON school_user (user_id) WHERE role = 'teacher';");
    }

    public function down(): void
    {
        // Drop partial index explicitly for PostgreSQL
        DB::statement("DROP INDEX IF EXISTS school_user_teacher_unique;");
        Schema::dropIfExists('school_user');
    }
};
