<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'objectives',
        'category',
        'tags',
        'has_certificate',
        'status',
        'enrollment_type',
        'navigation_mode',
        'passing_threshold',
        'requires_quiz_pass',
        'is_active',
        'is_paid',
        'price',
        'currency',
    ];

    protected $casts = [
        'tags' => 'array',
        'has_certificate' => 'boolean',
        'requires_quiz_pass' => 'boolean',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function modules() {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function purchases()
    {
        return $this->hasMany(CoursePurchase::class);
    }
}
