<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('email');
            $table->string('subject')->nullable()->after('nip'); // mata pelajaran
            $table->string('class_name')->nullable()->after('subject'); // kelas
            $table->string('avatar')->nullable()->after('class_name');

            // Google OAuth
            $table->string('google_id')->nullable()->unique()->after('avatar');
            $table->text('google_access_token')->nullable()->after('google_id');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
            $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nip', 'subject', 'class_name', 'avatar', 'google_id', 'google_access_token', 'google_refresh_token', 'google_token_expires_at'
            ]);
        });
    }
};
