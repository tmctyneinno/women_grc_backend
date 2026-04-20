<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipMilestone extends Model
{
    protected $fillable = [
        'mentorship_id',
        'title',
        'description',
        'due_date',
        'completed_at',
        'status',
        'order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }
}
