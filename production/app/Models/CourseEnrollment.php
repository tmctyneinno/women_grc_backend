<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'completion_percentage',
        'time_spent_minutes',
        'last_module_id',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lastModule()
    {
        return $this->belongsTo(Module::class, 'last_module_id');
    }

    public function moduleProgress()
    {
        return $this->hasMany(ModuleProgress::class);
    }
}

