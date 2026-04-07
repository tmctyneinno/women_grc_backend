<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class Forum extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'type',
        'tags',
        'region_based',
        'region',
        'status',
        'admin_id',
        'closed_at',
        'archived_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'region_based' => 'boolean',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function memberships()
    {
        return $this->hasMany(ForumMembership::class);
    }

    public function invitations()
    {
        return $this->hasMany(ForumInvitation::class);
    }

    public function threads()
    {
        return $this->hasMany(ForumThread::class);
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class);
    }
}
