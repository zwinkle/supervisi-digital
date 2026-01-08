<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi massal.
     * Menyimpan metadata file dari Google Drive.
     */
    protected $fillable = [
        'owner_user_id',
        'schedule_id',
        'google_file_id',
        'name',
        'mime',
        'web_view_link',
        'web_content_link',
        'folder_id', // ID folder di Google Drive
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    /**
     * Pemilik file (User yang upload).
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Jadwal terkait file ini (jika ada).
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
