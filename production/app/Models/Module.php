<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'position',
        'is_active',
        'require_quiz_to_unlock',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_quiz_to_unlock' => 'boolean',
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function lessons() {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function quizzes() {
        return $this->hasMany(Quiz::class);
    }

    public function progressRecords()
    {
        return $this->hasMany(ModuleProgress::class);
    }
}
