<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipSetting extends Model
{
    protected $fillable = [
        'max_mentees_per_mentor',
        'default_duration_months',
        'reminder_intervals',
    ];

    protected $casts = [
        'max_mentees_per_mentor' => 'integer',
        'default_duration_months' => 'integer',
        'reminder_intervals' => 'array',
    ];
}
