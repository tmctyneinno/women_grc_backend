<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipFeedback extends Model
{
    protected $fillable = [
        'mentorship_id',
        'user_id',
        'role',
        'communication_quality',
        'goal_achievement',
        'engagement_frequency',
        'satisfaction',
        'comments',
        'submitted_at',
    ];

    protected $casts = [
        'communication_quality' => 'integer',
        'goal_achievement' => 'integer',
        'engagement_frequency' => 'integer',
        'satisfaction' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
