<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $articles = Article::query()
            ->where('status', 'published')
            ->with(['creatorUser:id,first_name,last_name', 'creatorAdmin:id,name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($nested) use ($q) {
                    $nested->where('title', 'like', "%{$q}%")
                        ->orWhere('summary', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%")
                        ->orWhere('tag', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20);

        return ApiResponse::success($articles, 'Articles fetched successfully.');
    }

    public function show(Article $article)
    {
        if ($article->status !== 'published') {
            return ApiResponse::error('Article not available.', [], 404);
        }

        $article->load(['creatorUser:id,first_name,last_name', 'creatorAdmin:id,name']);
        return ApiResponse::success($article, 'Article fetched successfully.');
    }

    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'tag' => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('articles/covers', 'public');
        }

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'tag' => $validated['tag'] ?? null,
            'cover_image' => $coverPath,
            'status' => 'pending',
            'created_by_user_id' => $user->id,
        ]);

        return ApiResponse::success($article, 'Article submitted for review.');
    }
}
