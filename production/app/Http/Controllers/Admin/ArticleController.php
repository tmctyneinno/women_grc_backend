<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\AdminActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    private function isSuperAdmin(): bool
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return false;
        }

        if (method_exists($admin, 'isSuperAdmin')) {
            return (bool) $admin->isSuperAdmin();
        }

        return strtolower((string) $admin->email) === 'enquiries@wgrcfp.org';
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $articles = Article::query()
            ->with(['creatorUser:id,first_name,last_name,email', 'creatorAdmin:id,name,email', 'approver:id,name,email'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($nested) use ($q) {
                    $nested->where('title', 'like', "%{$q}%")
                        ->orWhere('summary', 'like', "%{$q}%")
                        ->orWhere('tag', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles', 'status', 'q'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'tag' => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|max:4096',
        ];

        if ($this->isSuperAdmin()) {
            $rules['status'] = 'required|in:draft,published';
        }

        $validated = $request->validate($rules);

        $coverPath = $request->file('cover_image')
            ? $request->file('cover_image')->store('articles/covers', 'public')
            : null;

        $status = $this->isSuperAdmin() ? ($validated['status'] ?? 'draft') : 'pending';

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'tag' => $validated['tag'] ?? null,
            'status' => $status,
            'cover_image' => $coverPath,
            'created_by_admin_id' => auth('admin')->id(),
            'approved_by_admin_id' => $status === 'published' ? auth('admin')->id() : null,
            'approved_at' => $status === 'published' ? now() : null,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        AdminActivityService::log(auth('admin')->user(), 'article_create', $article, [], 'Created article');

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'tag' => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|max:4096',
        ];

        if ($this->isSuperAdmin()) {
            $rules['status'] = 'required|in:draft,published,pending,rejected';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }
            $article->cover_image = $request->file('cover_image')->store('articles/covers', 'public');
        }

        $status = $article->status;
        if ($this->isSuperAdmin()) {
            $status = $validated['status'];
        } elseif ($article->status !== 'published') {
            $status = 'pending';
        }

        $article->fill([
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'tag' => $validated['tag'] ?? null,
            'status' => $status,
        ]);

        if ($status === 'published' && !$article->published_at) {
            $article->published_at = now();
            $article->approved_by_admin_id = auth('admin')->id();
            $article->approved_at = now();
        }

        $article->save();
        AdminActivityService::log(auth('admin')->user(), 'article_update', $article, [], 'Updated article');

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    public function approve(Article $article)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $article->update([
            'status' => 'published',
            'approved_by_admin_id' => auth('admin')->id(),
            'approved_at' => now(),
            'published_at' => $article->published_at ?: now(),
        ]);
        AdminActivityService::log(auth('admin')->user(), 'article_approve', $article, [], 'Approved article');

        return back()->with('success', 'Article approved.');
    }

    public function reject(Article $article)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $article->update([
            'status' => 'rejected',
            'approved_by_admin_id' => auth('admin')->id(),
            'approved_at' => now(),
        ]);
        AdminActivityService::log(auth('admin')->user(), 'article_reject', $article, [], 'Rejected article');

        return back()->with('success', 'Article rejected.');
    }

    public function destroy(Article $article)
    {
        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }
        $article->delete();
        AdminActivityService::log(auth('admin')->user(), 'article_delete', $article, [], 'Deleted article');

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
