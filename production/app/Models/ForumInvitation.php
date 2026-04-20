<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class ForumInvitation extends Model
{
    protected $fillable = [
        'forum_id',
        'admin_id',
        'invited_by',
        'invited_user_id',
        'status',
        'responded_at',
        'token',
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

    public function adminInviter()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }
}
