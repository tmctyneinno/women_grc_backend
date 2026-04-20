<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipSession extends Model
{
    protected $fillable = [
        'mentorship_id',
        'scheduled_at',
        'duration_minutes',
        'meeting_link',
        'status',
        'notes_shared',
        'mentor_notes',
        'mentee_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }
}
