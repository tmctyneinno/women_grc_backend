<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ForumMembership;
use App\Models\ForumNotification;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\ForumInvitation;
use App\Services\AdminActivityService;
use App\Models\Timezone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $forums = Forum::query()
            ->with('creator:id,name,email,role,is_active')
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

    public function create()
    {
        $timezones = Timezone::orderBy('timezone', 'asc')->get();
        return view('admin.forums.create', compact('timezones'));
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:120',
            'type' => 'required|in:public,private',
            'tags' => 'nullable|string',
            'region_based' => 'nullable|boolean',
            'region' => 'nullable|string|max:125|exists:timezone,timezone',
        ]);

        $tags = array_values(array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? '')))));

        $forum = DB::transaction(function () use ($data, $admin, $tags) {
            $forum = Forum::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'type' => $data['type'],
                'tags' => $tags,
                'region_based' => (bool) ($data['region_based'] ?? false),
                'region' => $data['region'] ?? null,
                'admin_id' => $admin?->id,
            ]);
            return $forum;
        });

        AdminActivityService::log(auth('admin')->user(), 'forum_create', $forum, [], 'Created forum');

        return redirect()->route('admin.forums.show', $forum)->with('success', 'Forum created successfully.');
    }

    public function show(Forum $forum)
    {
        $forum->load([
            'creator:id,name,email,role,is_active,created_at',
            'memberships' => fn ($query) => $query->with('user:id,first_name,last_name,email,status')
                ->where('status', 'active')
                ->orderByRaw("FIELD(role, 'creator', 'moderator', 'member')"),
            'threads' => fn ($query) => $query->with('user:id,first_name,last_name,email')
                ->where('is_blocked', false)
                ->latest()
                ->limit(20),
        ])->loadCount([
            'memberships as members_count' => fn ($query) => $query->where('status', 'active'),
            'threads',
            'posts',
        ]);

        $pendingMemberships = $forum->memberships()
            ->with('user:id,first_name,last_name,email,status')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $pendingInvites = ForumInvitation::where('forum_id', $forum->id)
            ->where('status', 'pending')
            ->with('invitedUser:id,first_name,last_name,email')
            ->latest()
            ->get();

        $blockedThreads = ForumThread::where('forum_id', $forum->id)
            ->where('is_blocked', true)
            ->with('user:id,first_name,last_name,email')
            ->latest()
            ->limit(20)
            ->get();

        $blockedPosts = ForumPost::where('forum_id', $forum->id)
            ->where('is_blocked', true)
            ->with(['user:id,first_name,last_name,email', 'thread:id,title'])
            ->latest()
            ->limit(20)
            ->get();

        $blockedThreadsCount = ForumThread::where('forum_id', $forum->id)->where('is_blocked', true)->count();
        $blockedPostsCount = ForumPost::where('forum_id', $forum->id)->where('is_blocked', true)->count();

        return view('admin.forums.show', compact(
            'forum',
            'pendingMemberships',
            'pendingInvites',
            'blockedThreads',
            'blockedPosts',
            'blockedThreadsCount',
            'blockedPostsCount'
        ));
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

    public function approveMembership(Forum $forum, ForumMembership $membership)
    {
        if ((int) $membership->forum_id !== (int) $forum->id) {
            return back()->withErrors(['membership' => 'Membership does not belong to this forum.']);
        }

        if ($membership->status !== 'pending') {
            return back()->withErrors(['membership' => 'Membership request is not pending.']);
        }

        $membership->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);

        ForumNotification::create([
            'user_id' => $membership->user_id,
            'forum_id' => $forum->id,
            'type' => 'forum_request_approved',
            'title' => 'Forum request approved',
            'body' => "Your request to join '{$forum->title}' was approved.",
            'data' => ['forum_id' => $forum->id],
        ]);

        AdminActivityService::log(auth('admin')->user(), 'forum_membership_approve', $forum, ['user_id' => $membership->user_id], 'Approved forum membership request');

        return back()->with('success', 'Membership request approved.');
    }

    public function rejectMembership(Forum $forum, ForumMembership $membership)
    {
        if ((int) $membership->forum_id !== (int) $forum->id) {
            return back()->withErrors(['membership' => 'Membership does not belong to this forum.']);
        }

        if ($membership->status !== 'pending') {
            return back()->withErrors(['membership' => 'Membership request is not pending.']);
        }

        $membership->update([
            'status' => 'rejected',
            'joined_at' => null,
        ]);

        ForumNotification::create([
            'user_id' => $membership->user_id,
            'forum_id' => $forum->id,
            'type' => 'forum_request_rejected',
            'title' => 'Forum request rejected',
            'body' => "Your request to join '{$forum->title}' was rejected.",
            'data' => ['forum_id' => $forum->id],
        ]);

        AdminActivityService::log(auth('admin')->user(), 'forum_membership_reject', $forum, ['user_id' => $membership->user_id], 'Rejected forum membership request');

        return back()->with('success', 'Membership request rejected.');
    }

    public function inviteByEmail(Request $request, Forum $forum)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.'])->withInput();
        }

        $alreadyMember = ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
        if ($alreadyMember) {
            return back()->withErrors(['email' => 'User is already a forum member.'])->withInput();
        }

        $token = Str::random(60);
        $invitation = ForumInvitation::updateOrCreate(
            ['forum_id' => $forum->id, 'invited_user_id' => $user->id, 'status' => 'pending'],
            [
                'admin_id' => auth('admin')->id(),
                'invited_by' => null,
                'token' => $token,
                'responded_at' => null,
            ]
        );

        if ($invitation->token !== $token) {
            $invitation->update(['token' => $token]);
        }

        $acceptUrl = url()->to('/forums/invitations/accept/' . $invitation->token);

        try {
            Mail::raw(
                "You have been invited to join the forum '{$forum->title}'.\n\nAccept invitation: {$acceptUrl}",
                function ($mail) use ($user) {
                    $mail->to($user->email)->subject('WGRCFP Forum Invitation');
                }
            );
        } catch (\Throwable $e) {
            // Keep invite creation non-blocking if mail fails.
        }

        AdminActivityService::log(auth('admin')->user(), 'forum_invite_send', $forum, ['user_id' => $user->id], 'Sent forum invite');

        return back()->with('success', 'Invitation email sent.');
    }
}
