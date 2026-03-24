<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'created_by',
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
        return $this->belongsTo(User::class, 'created_by');
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

