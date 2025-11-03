<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            // Make google_file_id nullable to support external links
            $table->string('google_file_id')->nullable()->change();
            // Drop unique constraint if exists
            $table->dropUnique(['google_file_id']);
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('google_file_id')->nullable(false)->change();
            $table->unique('google_file_id');
        });
    }
};
