<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->string('category', 32)->index();
            $table->timestamps();
        });

        if (!Schema::hasTable('submissions')) {
            return;
        }

        $now = Carbon::now();
        $rows = DB::table('submissions')->get([
            'id',
            'rpp_file_id',
            'asesmen_file_id',
            'administrasi_file_id',
        ]);

        $inserts = [];

        foreach ($rows as $row) {
            if ($row->rpp_file_id) {
                $inserts[] = [
                    'submission_id' => $row->id,
                    'file_id' => $row->rpp_file_id,
                    'category' => 'rpp',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($row->asesmen_file_id) {
                $inserts[] = [
                    'submission_id' => $row->id,
                    'file_id' => $row->asesmen_file_id,
                    'category' => 'asesmen',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($row->administrasi_file_id) {
                $inserts[] = [
                    'submission_id' => $row->id,
                    'file_id' => $row->administrasi_file_id,
                    'category' => 'administrasi',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($inserts)) {
            DB::table('submission_documents')->insert($inserts);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_documents');
    }
};
