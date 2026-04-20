<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipApplication extends Model
{
    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'goals',
        'preferred_duration',
        'availability',
        'communication_method',
        'notes',
        'status',
        'mentor_feedback',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function mentorship()
    {
        return $this->hasOne(Mentorship::class, 'application_id');
    }
}
