<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'schedule_id',
        'google_file_id',
        'name',
        'mime',
        'web_view_link',
        'web_content_link',
        'folder_id',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
