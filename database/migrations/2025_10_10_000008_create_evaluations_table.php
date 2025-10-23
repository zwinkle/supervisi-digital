<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['rpp', 'pembelajaran', 'asesmen']);
            $table->json('breakdown'); // key->score or key->boolean depending on type
            $table->decimal('total_score', 8, 2)->nullable(); // normalized 0-100 for all types
            $table->string('category')->nullable(); // e.g., Kurang/Cukup/Baik/Sangat Baik for pembelajaran
            $table->timestamps();

            $table->unique(['schedule_id','teacher_id','type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
