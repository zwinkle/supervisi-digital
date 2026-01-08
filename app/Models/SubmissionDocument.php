<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionDocument extends Model
{
    use HasFactory;

    // Kategori dokumen yang diizinkan
    public const ALLOWED_CATEGORIES = ['rpp', 'asesmen', 'administrasi'];
    // Batas maksimal file per kategori
    public const MAX_PER_CATEGORY = 6;

    protected $fillable = [
        'submission_id',
        'file_id',
        'category',
    ];

    /**
     * Parent submission.
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * File fisik/metadata di database.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Accessor: Label kategori yang diformat.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'rpp' => 'RPP',
            'asesmen' => 'Asesmen',
            'administrasi' => 'Administrasi',
            default => ucfirst($this->category ?? 'Dokumen'),
        };
    }
}
