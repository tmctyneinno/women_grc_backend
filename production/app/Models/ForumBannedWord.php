<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumBannedWord extends Model
{
    protected $fillable = [
        'word',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
