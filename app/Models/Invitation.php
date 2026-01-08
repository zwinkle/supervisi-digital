<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'role',
        'school_ids',
        'token',
        'invited_by',
        'expires_at',
        'used_at',
        'teacher_type',     // Jenis guru (untuk undangan guru)
        'teacher_subject',  // Mapel (jika guru mapel)
        'teacher_class',    // Kelas (jika guru kelas)
    ];

    /**
     * Casting tipe data otomatis.
     */
    protected $casts = [
        'school_ids' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Boot model.
     * Generate UUID untuk undangan baru.
     */
    protected static function booted(): void
    {
        static::creating(function (self $invitation) {
            if (empty($invitation->uuid)) {
                $invitation->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relasi ke user yang membuat undangan.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
