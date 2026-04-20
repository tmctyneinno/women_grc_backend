<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipFile extends Model
{
    protected $fillable = [
        'mentorship_id',
        'session_id',
        'uploaded_by',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function session()
    {
        return $this->belongsTo(MentorshipSession::class, 'session_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
