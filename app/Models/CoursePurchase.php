<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'amount',
        'currency',
        'status',
        'payment_reference',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

