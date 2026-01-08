<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Atribut yang dapat diisi secara massal.
     * Termasuk data diri, kredensial, dan token Google OAuth.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'teacher_type', // Jenis guru (subject/class)
        'subject',      // Mata pelajaran (jika subject teacher)
        'class_name',   // Nama kelas (jika class teacher)
        'avatar',       // URL foto profil
        'google_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi (JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_access_token',
        'google_refresh_token',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_token_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot model.
     * Otomatis mengisi UUID saat pembuatan user baru.
     */
    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Menggunakan kolom UUID untuk route binding (bukan ID auto-increment).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Accessor: Label jenis guru yang mudah dibaca.
     * Contoh: 'Guru Mata Pelajaran' atau 'Guru Kelas'.
     */
    public function getTeacherTypeLabelAttribute(): ?string
    {
        $type = $this->resolveTeacherType();

        return match ($type) {
            'subject' => 'Guru Mata Pelajaran',
            'class' => 'Guru Kelas',
            default => null,
        };
    }

    /**
     * Accessor: Detail penugasan guru.
     * Mengembalikan nama mata pelajaran atau nama kelas.
     */
    public function getTeacherDetailLabelAttribute(): ?string
    {
        $type = $this->resolveTeacherType();

        return match ($type) {
            'subject' => $this->subject,
            'class' => $this->class_name ? 'Kelas ' . $this->class_name : null,
            default => null,
        };
    }

    // --- Helper Methods untuk Resolusi Tipe Guru ---

    public function getResolvedTeacherTypeAttribute(): ?string
    {
        return $this->resolveTeacherType();
    }

    public function getResolvedTeacherSubjectAttribute(): ?string
    {
        return $this->resolveTeacherType() === 'subject' ? $this->subject : null;
    }

    public function getResolvedTeacherClassAttribute(): ?string
    {
        return $this->resolveTeacherType() === 'class' ? $this->class_name : null;
    }

    /**
     * Menentukan tipe guru berdasarkan data yang tersedia.
     * Prioritas: field teacher_type -> subject -> class_name.
     */
    protected function resolveTeacherType(): ?string
    {
        if (!is_null($this->teacher_type)) {
            return $this->teacher_type;
        }

        if (!empty($this->subject)) {
            return 'subject';
        }

        if (!empty($this->class_name)) {
            return 'class';
        }

        return null;
    }

    // --- Relationships ---

    /**
     * Relasi ke model School.
     * User bisa memiliki peran (teacher/supervisor) di banyak sekolah (Many-to-Many).
     */
    public function schools()
    {
        return $this->belongsToMany(School::class)->withPivot('role')->withTimestamps();
    }

    /**
     * Relasi jadwal di mana user bertindak sebagai Supervisor.
     */
    public function supervisedSchedules()
    {
        return $this->hasMany(Schedule::class, 'supervisor_id');
    }

    /**
     * Relasi jadwal di mana user bertindak sebagai Guru yang disupervisi.
     */
    public function teachingSchedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }
}
