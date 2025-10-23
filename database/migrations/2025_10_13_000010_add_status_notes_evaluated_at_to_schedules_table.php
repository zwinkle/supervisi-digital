<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'status')) {
                $table->string('status', 32)->default('scheduled')->index();
            }
            if (!Schema::hasColumn('schedules', 'remarks')) {
                $table->text('remarks')->nullable();
            }
            if (!Schema::hasColumn('schedules', 'evaluated_at')) {
                $table->timestamp('evaluated_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'evaluated_at')) {
                $table->dropColumn('evaluated_at');
            }
            if (Schema::hasColumn('schedules', 'remarks')) {
                $table->dropColumn('remarks');
            }
            // keep status column if it already existed previously
        });
    }
};
