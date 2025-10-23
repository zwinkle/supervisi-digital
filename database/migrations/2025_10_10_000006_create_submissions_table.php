<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rpp_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('video_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id','teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
