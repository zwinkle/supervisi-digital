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

    protected $casts = [
        'breakdown' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
