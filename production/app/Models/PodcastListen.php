<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PodcastListen extends Model
{
    protected $fillable = [
        'user_id',
        'podcast_id',
        'last_position_seconds',
        'duration_seconds',
        'progress_seconds',
        'completed_at',
        'last_listened_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'last_listened_at' => 'datetime',
    ];

    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }
}
