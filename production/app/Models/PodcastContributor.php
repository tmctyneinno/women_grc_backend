<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastContributor extends Model
{
    protected $fillable = [
        'podcast_id',
        'name',
        'role',
        'photo_path',
    ];

    protected $appends = ['photo_url'];

    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        if (Str::startsWith($this->photo_path, ['http://', 'https://'])) {
            return $this->photo_path;
        }

        if (Str::startsWith($this->photo_path, '/storage/')) {
            return url($this->photo_path);
        }

        return url(Storage::url($this->photo_path));
    }
}
