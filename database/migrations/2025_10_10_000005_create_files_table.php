<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->string('google_file_id')->unique();
            $table->string('name');
            $table->string('mime');
            $table->string('web_view_link')->nullable();
            $table->string('web_content_link')->nullable();
            $table->string('folder_id')->nullable();
            $table->json('extra')->nullable(); // place to store metadata like videoMediaMetadata
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
