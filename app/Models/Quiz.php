<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['module_id','question','options','correct_answer'];
    protected $casts = ['options' => 'array'];
}
