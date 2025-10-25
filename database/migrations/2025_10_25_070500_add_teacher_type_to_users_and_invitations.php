<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'teacher_type')) {
                $table->string('teacher_type')->nullable()->after('nip');
            }
        });

        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'teacher_type')) {
                $table->string('teacher_type')->nullable()->after('role');
            }
            if (!Schema::hasColumn('invitations', 'teacher_subject')) {
                $table->string('teacher_subject')->nullable()->after('teacher_type');
            }
            if (!Schema::hasColumn('invitations', 'teacher_class')) {
                $table->string('teacher_class')->nullable()->after('teacher_subject');
            }
        });

        // Backfill teacher_type based on existing subject or class data
        DB::table('users')
            ->whereNull('teacher_type')
            ->whereNotNull('subject')
            ->update(['teacher_type' => 'subject']);

        DB::table('users')
            ->whereNull('teacher_type')
            ->whereNotNull('class_name')
            ->update(['teacher_type' => 'class']);
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'teacher_class')) {
                $table->dropColumn('teacher_class');
            }
            if (Schema::hasColumn('invitations', 'teacher_subject')) {
                $table->dropColumn('teacher_subject');
            }
            if (Schema::hasColumn('invitations', 'teacher_type')) {
                $table->dropColumn('teacher_type');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'teacher_type')) {
                $table->dropColumn('teacher_type');
            }
        });
    }
};
