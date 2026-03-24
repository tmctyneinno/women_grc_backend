<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    protected $fillable = [
        'forum_thread_id',
        'forum_id',
        'user_id',
        'parent_post_id',
        'quote_post_id',
        'content',
        'attachment_path',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(ForumPost::class, 'parent_post_id');
    }

    public function quote()
    {
        return $this->belongsTo(ForumPost::class, 'quote_post_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumPost::class, 'parent_post_id');
    }

    public function reactions()
    {
        return $this->hasMany(ForumReaction::class);
    }

    public function reports()
    {
        return $this->hasMany(ForumReport::class);
    }
}

