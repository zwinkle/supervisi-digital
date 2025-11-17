<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionDocument extends Model
{
    use HasFactory;

    public const ALLOWED_CATEGORIES = ['rpp', 'asesmen', 'administrasi'];
    public const MAX_PER_CATEGORY = 6;

    protected $fillable = [
        'submission_id',
        'file_id',
        'category',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

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
