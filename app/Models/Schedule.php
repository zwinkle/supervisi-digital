<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'supervisor_id', 'teacher_id', 'date', 'title', 'description', 'class_name', 'remarks', 'evaluated_at'
    ];

    protected $casts = [
        'date' => 'date',
        'evaluated_at' => 'datetime',
        'conducted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $schedule) {
            if (empty($schedule->uuid)) {
                $schedule->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submission()
    {
        return $this->hasOne(Submission::class)->with(['documents.file','videoFile']);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->evaluated_at);
    }

    public function daysUntil(): ?int
    {
        if (!$this->date) return null;
        return Carbon::today()->diffInDays($this->date, false);
    }

    public function computedBadge(): array
    {
        // If session has been conducted, it's considered Selesai
        if ($this->conducted_at) {
            return ['text' => 'Selesai', 'class' => 'bg-green-100 text-green-800'];
        }
        // Today
        if ($this->date && $this->date->isToday()) {
            return ['text' => 'Hari ini', 'class' => 'bg-blue-100 text-blue-800'];
        }
        $du = $this->daysUntil();
        if ($du !== null && $du >= 1) {
            return ['text' => 'Dalam '.$du.' hari', 'class' => 'bg-amber-100 text-amber-800'];
        }
        // Past date and not conducted -> overdue
        if ($this->date && $this->date->isPast()) {
            return ['text' => 'Terlewat', 'class' => 'bg-red-100 text-red-800'];
        }
        return ['text' => 'Terjadwal', 'class' => 'bg-gray-100 text-gray-800'];
    }

    public function hasAllEvaluations(): bool
    {
        $types = $this->evaluations()->pluck('type')->unique()->all();
        return in_array('rpp', $types, true)
            && in_array('pembelajaran', $types, true)
            && in_array('asesmen', $types, true);
    }

    public function hasSubmissionFiles(): bool
    {
        $submission = $this->submission;
        if (!$submission) {
            return false;
        }

        return $submission->hasDocumentsFor('rpp')
            && $submission->hasDocumentsFor('asesmen')
            && $submission->hasDocumentsFor('administrasi')
            && (bool) optional($submission->videoFile)->id;
    }

    public function hasSubmissionFor(string $type): bool
    {
        $submission = $this->submission;
        if (!$submission) {
            return false;
        }

        return match ($type) {
            'rpp' => $submission->hasDocumentsFor('rpp'),
            'asesmen' => $submission->hasDocumentsFor('asesmen'),
            'administrasi' => $submission->hasDocumentsFor('administrasi'),
            'pembelajaran' => (bool) optional($submission->videoFile)->id,
            default => false,
        };
    }

    public function checkAndMarkCompleted(): void
    {
        if ($this->isCompleted()) return;
        $this->loadMissing([
            'evaluations',
            'submission.documents.file',
            'submission.videoFile',
        ]);
        if ($this->hasAllEvaluations() && $this->hasSubmissionFiles()) {
            $this->evaluated_at = Carbon::now();
            $this->save();
        }
    }
}
