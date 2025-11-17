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

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function documents()
    {
        return $this->hasMany(SubmissionDocument::class);
    }

    public function videoFile()
    {
        return $this->belongsTo(File::class, 'video_file_id');
    }

    public function documentsFor(string $category)
    {
        if ($this->relationLoaded('documents')) {
            return $this->documents->where('category', $category);
        }

        return $this->documents()->where('category', $category)->get();
    }

    public function hasDocumentsFor(string $category): bool
    {
        if ($this->relationLoaded('documents')) {
            return $this->documents->where('category', $category)->isNotEmpty();
        }

        return $this->documents()->where('category', $category)->exists();
    }
}
