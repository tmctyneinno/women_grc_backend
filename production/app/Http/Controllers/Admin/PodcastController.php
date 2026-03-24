<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastContributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastController extends Controller
{
    public function index()
    {
        $podcasts = Podcast::withCount('contributors')->latest()->paginate(15);
        return view('admin.podcasts.index', compact('podcasts'));
    }

    public function create()
    {
        return view('admin.podcasts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'tag' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'is_active' => 'nullable|boolean',
            'audio_file' => 'required|file|mimes:mp3,wav,m4a,ogg,webm',
            'cover_image' => 'nullable|image|max:4096',
        ]);

        $audioPath = $request->file('audio_file')->store('podcasts/audio', 'public');
        $coverPath = $request->file('cover_image')
            ? $request->file('cover_image')->store('podcasts/covers', 'public')
            : null;

        $podcast = Podcast::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'tag' => $validated['tag'] ?? null,
            'status' => $validated['status'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'audio_path' => $audioPath,
            'cover_path' => $coverPath,
        ]);

        $this->syncContributors($request, $podcast);

        return redirect()
            ->route('admin.podcasts.index')
            ->with('success', 'Podcast created successfully.');
    }

    public function edit(Podcast $podcast)
    {
        $podcast->load('contributors');
        return view('admin.podcasts.edit', compact('podcast'));
    }

    public function update(Request $request, Podcast $podcast)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'tag' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'is_active' => 'nullable|boolean',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a,ogg,webm',
            'cover_image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('audio_file')) {
            if ($podcast->audio_path) {
                Storage::disk('public')->delete($podcast->audio_path);
            }
            $podcast->audio_path = $request->file('audio_file')->store('podcasts/audio', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($podcast->cover_path) {
                Storage::disk('public')->delete($podcast->cover_path);
            }
            $podcast->cover_path = $request->file('cover_image')->store('podcasts/covers', 'public');
        }

        $podcast->fill([
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'tag' => $validated['tag'] ?? null,
            'status' => $validated['status'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'published_at' => $validated['status'] === 'published'
                ? ($podcast->published_at ?: now())
                : null,
        ]);
        $podcast->save();

        $this->syncContributors($request, $podcast);

        return redirect()
            ->route('admin.podcasts.index')
            ->with('success', 'Podcast updated successfully.');
    }

    public function destroy(Podcast $podcast)
    {
        if ($podcast->audio_path) {
            Storage::disk('public')->delete($podcast->audio_path);
        }
        if ($podcast->cover_path) {
            Storage::disk('public')->delete($podcast->cover_path);
        }

        foreach ($podcast->contributors as $contributor) {
            if ($contributor->photo_path) {
                Storage::disk('public')->delete($contributor->photo_path);
            }
        }

        $podcast->delete();

        return redirect()
            ->route('admin.podcasts.index')
            ->with('success', 'Podcast deleted successfully.');
    }

    private function syncContributors(Request $request, Podcast $podcast): void
    {
        $ids = $request->input('contributor_id', []);
        $names = $request->input('contributor_name', []);
        $roles = $request->input('contributor_role', []);
        $photos = $request->file('contributor_photo', []);

        $keptIds = [];

        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $role = $roles[$index] ?? null;
            $photoFile = $photos[$index] ?? null;
            $id = $ids[$index] ?? null;

            if ($id) {
                $contributor = $podcast->contributors()->where('id', $id)->first();
            } else {
                $contributor = new PodcastContributor(['podcast_id' => $podcast->id]);
            }

            if (!$contributor) {
                $contributor = new PodcastContributor(['podcast_id' => $podcast->id]);
            }

            $contributor->name = $name;
            $contributor->role = $role;

            if ($photoFile) {
                if ($contributor->photo_path) {
                    Storage::disk('public')->delete($contributor->photo_path);
                }
                $contributor->photo_path = $photoFile->store('podcasts/contributors', 'public');
            }

            $contributor->podcast_id = $podcast->id;
            $contributor->save();
            $keptIds[] = $contributor->id;
        }

        $podcast->contributors()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(function (PodcastContributor $contributor) {
                if ($contributor->photo_path) {
                    Storage::disk('public')->delete($contributor->photo_path);
                }
                $contributor->delete();
            });
    }
}
