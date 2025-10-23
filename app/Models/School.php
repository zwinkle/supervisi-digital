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

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
