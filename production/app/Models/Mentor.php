<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mentor extends Model
{
    use SoftDeletes;

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
        'mentorships_completed',
        'rating_avg',
        'max_mentees',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'languages' => 'array',
        'skills' => 'array',
        'certifications' => 'array',
        'mentorships_completed' => 'integer',
        'rating_avg' => 'decimal:2',
        'max_mentees' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(MentorshipApplication::class);
    }

    public function mentorships()
    {
        return $this->hasMany(Mentorship::class);
    }
}
