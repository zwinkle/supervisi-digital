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
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('asesmen_file_id')->nullable()->after('video_file_id')->constrained('files')->nullOnDelete();
            $table->foreignId('administrasi_file_id')->nullable()->after('asesmen_file_id')->constrained('files')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['asesmen_file_id']);
            $table->dropForeign(['administrasi_file_id']);
            $table->dropColumn(['asesmen_file_id', 'administrasi_file_id']);
        });
    }
};
