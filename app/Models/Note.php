<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id', 'supervisor_id', 'content', 'follow_up'
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
