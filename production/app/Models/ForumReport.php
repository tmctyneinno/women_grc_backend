<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumReport extends Model
{
    protected $fillable = [
        'forum_post_id',
        'reported_by',
        'reason',
        'details',
        'status',
    ];

    public function post()
    {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}

