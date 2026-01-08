<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'school_id', 'schedule_id', 'action', 'meta'
    ];

    /**
     * Casting 'meta' menjadi array (JSON).
     * Menyimpan detail tambahan aksi (contoh: data sebelum/sesudah).
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * User yang melakukan aktivitas.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sekolah terkait aktivitas (opsional).
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Jadwal terkait aktivitas (opsional).
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
