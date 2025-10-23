<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['users', 'schools', 'schedules', 'invitations'];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->uuid('uuid')->nullable()->after('id');
                });
            }
        }

        foreach ($tables as $table) {
            DB::table($table)
                ->select('id')
                ->whereNull('uuid')
                ->chunkById(200, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        }

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'schools', 'schedules', 'invitations'];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    $tableBlueprint->dropUnique($table.'_uuid_unique');
                    $tableBlueprint->dropColumn('uuid');
                });
            }
        }
    }
};
