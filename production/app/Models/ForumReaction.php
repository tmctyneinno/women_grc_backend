<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumReaction extends Model
{
    protected $fillable = [
        'forum_post_id',
        'user_id',
        'reaction',
    ];

    public function post()
    {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

