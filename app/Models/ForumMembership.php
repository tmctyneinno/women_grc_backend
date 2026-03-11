<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumMembership extends Model
{
    protected $fillable = [
        'forum_id',
        'user_id',
        'role',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

