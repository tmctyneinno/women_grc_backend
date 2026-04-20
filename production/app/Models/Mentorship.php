<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'application_id',
        'status',
        'start_date',
        'end_date',
        'duration_months',
        'goal_summary',
        'communication_method',
        'progress_percentage',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'duration_months' => 'integer',
        'progress_percentage' => 'decimal:2',
    ];

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function application()
    {
        return $this->belongsTo(MentorshipApplication::class, 'application_id');
    }

    public function milestones()
    {
        return $this->hasMany(MentorshipMilestone::class);
    }

    public function sessions()
    {
        return $this->hasMany(MentorshipSession::class);
    }

    public function notes()
    {
        return $this->hasMany(MentorshipNote::class);
    }

    public function files()
    {
        return $this->hasMany(MentorshipFile::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(MentorshipFeedback::class);
    }
}
