<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // schedules: supervisor_id, teacher_id, school_id -> cascade on delete
        Schema::table('schedules', function (Blueprint $table) {
            // Drop existing FKs if any (by column)
            try { $table->dropForeign(['supervisor_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['teacher_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['school_id']); } catch (\Throwable $e) {}
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        // evaluations: schedule_id -> cascade on delete
        if (Schema::hasTable('evaluations')) {
            Schema::table('evaluations', function (Blueprint $table) {
                try { $table->dropForeign(['schedule_id']); } catch (\Throwable $e) {}
            });
            Schema::table('evaluations', function (Blueprint $table) {
                $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            });
        }

        // submissions: schedule_id -> cascade on delete (if table exists)
        if (Schema::hasTable('submissions')) {
            Schema::table('submissions', function (Blueprint $table) {
                try { $table->dropForeign(['schedule_id']); } catch (\Throwable $e) {}
            });
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Revert to restrict (no cascade) - set to RESTRICT by dropping and re-adding without onDelete
        Schema::table('schedules', function (Blueprint $table) {
            try { $table->dropForeign(['supervisor_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['teacher_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['school_id']); } catch (\Throwable $e) {}
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('supervisor_id')->references('id')->on('users');
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->foreign('school_id')->references('id')->on('schools');
        });

        if (Schema::hasTable('evaluations')) {
            Schema::table('evaluations', function (Blueprint $table) {
                try { $table->dropForeign(['schedule_id']); } catch (\Throwable $e) {}
            });
            Schema::table('evaluations', function (Blueprint $table) {
                $table->foreign('schedule_id')->references('id')->on('schedules');
            });
        }

        if (Schema::hasTable('submissions')) {
            Schema::table('submissions', function (Blueprint $table) {
                try { $table->dropForeign(['schedule_id']); } catch (\Throwable $e) {}
            });
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreign('schedule_id')->references('id')->on('schedules');
            });
        }
    }
};
