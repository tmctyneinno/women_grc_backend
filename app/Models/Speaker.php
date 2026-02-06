<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'title',
        'brief',
        'image',
        'order'
    ];

    protected $casts = [
        'order' => 'integer'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/speakers/' . $this->image);
        }
        return null;
    }
}