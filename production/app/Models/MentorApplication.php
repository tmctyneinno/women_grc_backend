<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorApplication extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'domain',
        'region',
        'country',
        'bio',
        'expertise_summary',
        'availability_status',
        'languages',
        'skills',
        'certifications',
        'max_mentees',
        'status',
        'admin_notes',
        'decided_at',
    ];

    protected $casts = [
        'languages' => 'array',
        'skills' => 'array',
        'certifications' => 'array',
        'max_mentees' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
