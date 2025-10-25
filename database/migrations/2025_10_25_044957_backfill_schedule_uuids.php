<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('schedules')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(100, function ($schedules) {
                foreach ($schedules as $schedule) {
                    DB::table('schedules')
                        ->where('id', $schedule->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
