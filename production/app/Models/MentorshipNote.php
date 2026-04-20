<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipNote extends Model
{
    protected $fillable = [
        'mentorship_id',
        'session_id',
        'author_id',
        'visibility',
        'content',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function session()
    {
        return $this->belongsTo(MentorshipSession::class, 'session_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
