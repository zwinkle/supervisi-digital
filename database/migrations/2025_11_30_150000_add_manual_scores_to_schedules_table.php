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
            $table->decimal('manual_rpp_score', 5, 2)->nullable()->after('evaluation_method');
            $table->decimal('manual_pembelajaran_score', 5, 2)->nullable()->after('manual_rpp_score');
            $table->decimal('manual_asesmen_score', 5, 2)->nullable()->after('manual_pembelajaran_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['manual_rpp_score', 'manual_pembelajaran_score', 'manual_asesmen_score']);
        });
    }
};
