<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Podcast extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'audio_path',
        'cover_path',
        'duration',
        'tag',
        'status',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = ['audio_url', 'cover_url'];

    public function contributors()
    {
        return $this->hasMany(PodcastContributor::class);
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->audio_path);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->cover_path);
    }

    protected function resolveStorageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, '/storage/')) {
            return url($path);
        }

        return url(Storage::url($path));
    }
}
