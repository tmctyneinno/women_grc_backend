<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'tag',
        'status',
        'created_by_user_id',
        'created_by_admin_id',
        'approved_by_admin_id',
        'approved_at',
        'published_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function creatorAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by_admin_id');
    }
}
