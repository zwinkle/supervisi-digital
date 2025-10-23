<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'status')) {
                $table->string('status', 32)->default('scheduled')->index();
            }
        });
    }
};
