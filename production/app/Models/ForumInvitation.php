<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumInvitation extends Model
{
    protected $fillable = [
        'forum_id',
        'invited_by',
        'invited_user_id',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }
}

