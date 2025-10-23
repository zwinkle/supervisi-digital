<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // MySQL default collations are case-insensitive, so this enforces uniqueness per supervisor+date+title
            $table->unique(['supervisor_id', 'date', 'title'], 'schedules_supervisor_date_title_unique');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique('schedules_supervisor_date_title_unique');
        });
    }
};
