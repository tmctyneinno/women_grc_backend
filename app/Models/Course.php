<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title','description','objectives','category','tags','has_certificate','status'
    ];

    protected $casts = [
        'tags' => 'array',
        'has_certificate' => 'boolean',
    ];

    public function modules() {
        return $this->hasMany(Module::class);
    }
}
