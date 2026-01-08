<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'created_by'
    ];

    /**
     * Boot model.
     * Generate UUID saat pembuatan sekolah.
     */
    protected static function booted(): void
    {
        static::creating(function (self $school) {
            if (empty($school->uuid)) {
                $school->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relasi ke User (Guru/Supervisor).
     * Many-to-Many dengan pivot table 'school_user' yang menyimpan role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    /**
     * Relasi ke Jadwal Supervisi yang terkait dengan sekolah ini.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
