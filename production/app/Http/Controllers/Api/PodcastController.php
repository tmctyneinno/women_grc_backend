<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastListen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PodcastController extends Controller
{
    public function index(Request $request)
    {
        $query = Podcast::query()
            ->with('contributors')
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if ($request->filled('tag')) {
            $query->where('tag', $request->query('tag'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $podcasts = $query->paginate(20);

        return ApiResponse::success($podcasts, 'Podcasts fetched successfully.');
    }

    public function show(Podcast $podcast)
    {
        if (!$podcast->is_active || $podcast->status !== 'published') {
            return ApiResponse::error('Podcast not available.', [], 404);
        }

        $podcast->load('contributors');

        return ApiResponse::success($podcast, 'Podcast fetched successfully.');
    }

    public function progress()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $progress = PodcastListen::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('podcast_id')
            ->map(function (PodcastListen $listen) {
                return [
                    'podcast_id' => $listen->podcast_id,
                    'last_position_seconds' => $listen->last_position_seconds,
                    'duration_seconds' => $listen->duration_seconds,
                    'progress_seconds' => $listen->progress_seconds,
                    'completed_at' => optional($listen->completed_at)->toDateTimeString(),
                    'last_listened_at' => optional($listen->last_listened_at)->toDateTimeString(),
                ];
            })
            ->values();

        return ApiResponse::success($progress, 'Podcast progress fetched.');
    }

    public function updateProgress(Request $request, Podcast $podcast)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $data = $request->validate([
            'last_position_seconds' => 'required|integer|min:0',
            'duration_seconds' => 'nullable|integer|min:0',
            'progress_seconds' => 'nullable|integer|min:0',
            'completed' => 'nullable|boolean',
        ]);

        $listen = PodcastListen::updateOrCreate(
            [
                'user_id' => $user->id,
                'podcast_id' => $podcast->id,
            ],
            [
                'last_position_seconds' => $data['last_position_seconds'],
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'progress_seconds' => $data['progress_seconds'] ?? $data['last_position_seconds'],
                'completed_at' => ($data['completed'] ?? false) ? now() : null,
                'last_listened_at' => now(),
            ]
        );

        return ApiResponse::success([
            'podcast_id' => $listen->podcast_id,
            'last_position_seconds' => $listen->last_position_seconds,
            'duration_seconds' => $listen->duration_seconds,
            'progress_seconds' => $listen->progress_seconds,
            'completed_at' => optional($listen->completed_at)->toDateTimeString(),
            'last_listened_at' => optional($listen->last_listened_at)->toDateTimeString(),
        ], 'Podcast progress saved.');
    }
}
