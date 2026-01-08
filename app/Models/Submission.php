<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'video_file_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // --- Relationships ---

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Dokumen pendukung (RPP, Asesmen, Administrasi).
     */
    public function documents()
    {
        return $this->hasMany(SubmissionDocument::class);
    }

    /**
     * File video bukti pembelajaran.
     */
    public function videoFile()
    {
        return $this->belongsTo(File::class, 'video_file_id');
    }

    // --- Helper Methods ---

    /**
     * Ambil dokumen berdasarkan kategori (rpp, asesmen, dsb).
     * Mengecek relation loaded untuk optimalisasi query.
     */
    public function documentsFor(string $category)
    {
        if ($this->relationLoaded('documents')) {
            return $this->documents->where('category', $category);
        }

        return $this->documents()->where('category', $category)->get();
    }

    /**
     * Cek apakah kategori dokumen tertentu sudah ada isinya.
     */
    public function hasDocumentsFor(string $category): bool
    {
        if ($this->relationLoaded('documents')) {
            return $this->documents->where('category', $category)->isNotEmpty();
        }

        return $this->documents()->where('category', $category)->exists();
    }
}
