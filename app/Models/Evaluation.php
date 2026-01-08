<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id', 'teacher_id', 'type', 'breakdown', 'total_score', 'category'
    ];

    /**
     * Casting 'breakdown' menjadi array (disimpan sebagai JSON).
     * Berisi detail skor per item penilaian.
     */
    protected $casts = [
        'breakdown' => 'array',
    ];

    /**
     * Relasi ke Jadwal Supervisi.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Relasi ke Guru yang dinilai.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
