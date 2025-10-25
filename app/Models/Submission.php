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
        'rpp_file_id',
        'video_file_id',
        'asesmen_file_id',
        'administrasi_file_id',
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

    public function rppFile()
    {
        return $this->belongsTo(File::class, 'rpp_file_id');
    }

    public function videoFile()
    {
        return $this->belongsTo(File::class, 'video_file_id');
    }

    public function asesmenFile()
    {
        return $this->belongsTo(File::class, 'asesmen_file_id');
    }

    public function administrasiFile()
    {
        return $this->belongsTo(File::class, 'administrasi_file_id');
    }
}
