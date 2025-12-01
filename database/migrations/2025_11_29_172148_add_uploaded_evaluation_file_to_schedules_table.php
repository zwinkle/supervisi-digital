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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('uploaded_evaluation_file')->nullable()->after('evaluated_at');
            $table->string('evaluation_method')->default('manual')->after('uploaded_evaluation_file'); // 'manual' or 'upload'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['uploaded_evaluation_file', 'evaluation_method']);
        });
    }
};
