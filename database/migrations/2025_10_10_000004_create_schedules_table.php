<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('scheduled'); // scheduled|upcoming|completed
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
