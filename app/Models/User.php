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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'teacher_type',
        'subject',
        'class_name',
        'avatar',
        'google_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
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
     * Get the attributes that should be cast.
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

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getTeacherTypeLabelAttribute(): ?string
    {
        $type = $this->resolveTeacherType();

        return match ($type) {
            'subject' => 'Guru Mata Pelajaran',
            'class' => 'Guru Kelas',
            default => null,
        };
    }

    public function getTeacherDetailLabelAttribute(): ?string
    {
        $type = $this->resolveTeacherType();

        return match ($type) {
            'subject' => $this->subject,
            'class' => $this->class_name ? 'Kelas ' . $this->class_name : null,
            default => null,
        };
    }

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

    // Relationships
    public function schools()
    {
        return $this->belongsToMany(School::class)->withPivot('role')->withTimestamps();
    }

    public function supervisedSchedules()
    {
        return $this->hasMany(Schedule::class, 'supervisor_id');
    }

    public function teachingSchedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }
}
