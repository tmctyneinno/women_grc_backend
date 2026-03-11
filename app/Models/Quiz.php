<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'module_id',
        'question',
        'question_type',
        'options',
        'correct_answer',
        'passing_threshold',
        'max_attempts',
        'show_feedback',
    ];

    protected $casts = [
        'options' => 'array',
        'show_feedback' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
