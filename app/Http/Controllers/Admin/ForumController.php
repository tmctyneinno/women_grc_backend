<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Services\AdminActivityService;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $forums = Forum::query()
            ->with('creator:id,first_name,last_name,email,status')
            ->withCount([
                'memberships as members_count' => fn ($query) => $query->where('status', 'active'),
                'threads',
                'posts',
            ])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($nested) use ($q) {
                    $nested->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.forums.index', compact('forums', 'status', 'q'));
    }

    public function show(Forum $forum)
    {
        $forum->load([
            'creator:id,first_name,last_name,email,status,created_at',
            'memberships' => fn ($query) => $query->with('user:id,first_name,last_name,email,status')
                ->where('status', 'active')
                ->orderByRaw("FIELD(role, 'creator', 'moderator', 'member')"),
            'threads' => fn ($query) => $query->with('user:id,first_name,last_name,email')->latest()->limit(20),
        ])->loadCount([
            'memberships as members_count' => fn ($query) => $query->where('status', 'active'),
            'threads',
            'posts',
        ]);

        return view('admin.forums.show', compact('forum'));
    }

    public function deactivate(Forum $forum)
    {
        $forum->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
        AdminActivityService::log(auth('admin')->user(), 'forum_deactivate', $forum, [], 'Deactivated forum');

        return back()->with('success', 'Forum has been deactivated (archived).');
    }

    public function activate(Forum $forum)
    {
        $forum->update([
            'status' => 'open',
            'archived_at' => null,
            'closed_at' => null,
        ]);
        AdminActivityService::log(auth('admin')->user(), 'forum_activate', $forum, [], 'Activated forum');

        return back()->with('success', 'Forum has been activated.');
    }

    public function destroy(Forum $forum)
    {
        $forum->delete();
        AdminActivityService::log(auth('admin')->user(), 'forum_delete', $forum, [], 'Deleted forum');

        return redirect()->route('admin.forums.index')->with('success', 'Forum deleted successfully.');
    }
}
