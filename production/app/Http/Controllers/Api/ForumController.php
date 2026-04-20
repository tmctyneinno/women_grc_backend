<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ForumInvitation;
use App\Models\ForumMembership;
use App\Models\ForumNotification;
use App\Models\ForumPost;
use App\Models\ForumReaction;
use App\Models\ForumReport;
use App\Models\ForumThread;
use App\Models\ForumBannedWord;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $q = trim((string) $request->query('q', ''));
        $mine = (bool) $request->boolean('mine');

        $forums = Forum::query()
            ->withCount([
                'memberships as members_count' => fn ($query) => $query->where('status', 'active'),
                'threads as threads_count' => fn ($query) => $query->where('is_blocked', false),
            ])
            ->addSelect([
                'has_pending_request' => ForumMembership::selectRaw('count(*)')
                    ->whereColumn('forum_memberships.forum_id', 'forums.id')
                    ->where('user_id', $user->id)
                    ->where('status', 'pending'),
            ])
            ->where('status', 'open')
            ->when($mine, function ($query) use ($user) {
                $query->whereHas('memberships', function ($memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->id)->where('status', 'active');
                });
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($nested) use ($q) {
                    $nested->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20);

        return ApiResponse::success($forums, 'Forums fetched successfully.');
    }

    public function store(Request $request)
    {
        return ApiResponse::error('Forum creation is restricted to admins.', [], 403);
    }

    public function show(Forum $forum)
    {
        $user = $this->authUser();
        if ($forum->status !== 'open') {
            return ApiResponse::error('This forum is not available.', [], 403);
        }
        if (!$this->canViewForum($forum, $user)) {
            return ApiResponse::error('You do not have access to this forum.', [], 403);
        }

        $forum->load([
            'memberships' => fn ($query) => $query->with('user:id,first_name,last_name')->where('status', 'active'),
            'threads' => fn ($query) => $query->with('user:id,first_name,last_name')
                ->where('is_blocked', false)
                ->withCount(['posts' => fn ($postQuery) => $postQuery->where('is_blocked', false)])
                ->orderByDesc('is_pinned')
                ->latest()
                ->limit(30),
        ]);

        return ApiResponse::success($forum, 'Forum details fetched successfully.');
    }

    public function update(Request $request, Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isModeratorOrCreator($forum, $user)) {
            return ApiResponse::error('Only creator or moderators can edit forum settings.', [], 403);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:120',
            'type' => 'nullable|in:public,private',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:80',
            'region_based' => 'nullable|boolean',
            'region' => 'nullable|string|max:120',
        ]);

        $forum->update($data);

        return ApiResponse::success($forum->fresh(), 'Forum updated successfully.');
    }

    public function close(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isCreator($forum, $user)) {
            return ApiResponse::error('Only the forum creator can close this forum.', [], 403);
        }

        $forum->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return ApiResponse::success($forum, 'Forum closed successfully.');
    }

    public function archive(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isCreator($forum, $user)) {
            return ApiResponse::error('Only the forum creator can archive this forum.', [], 403);
        }

        $forum->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        return ApiResponse::success($forum, 'Forum archived successfully.');
    }

    public function reopen(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isCreator($forum, $user)) {
            return ApiResponse::error('Only the forum creator can reopen this forum.', [], 403);
        }

        $forum->update([
            'status' => 'open',
            'closed_at' => null,
            'archived_at' => null,
        ]);

        return ApiResponse::success($forum, 'Forum reopened successfully.');
    }

    public function destroy(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isCreator($forum, $user)) {
            return ApiResponse::error('Only the forum creator can delete this forum.', [], 403);
        }

        $forum->delete();

        return ApiResponse::success([], 'Forum deleted successfully.');
    }

    public function join(Forum $forum)
    {
        $user = $this->authUser();
        if ($forum->status !== 'open') {
            return ApiResponse::error('This forum is not available.', [], 403);
        }
        $membership = ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->first();

        if ($membership && $membership->status === 'active') {
            return ApiResponse::error('You are already a member of this forum.', [], 409);
        }

        if ($membership && $membership->status === 'pending') {
            return ApiResponse::success($membership, 'Your request is still pending.');
        }

        $membership = ForumMembership::updateOrCreate(
            ['forum_id' => $forum->id, 'user_id' => $user->id],
            ['status' => 'pending', 'role' => 'member', 'joined_at' => null]
        );

        $this->notifyAdmins(
            "New forum join request",
            "{$user->first_name} {$user->last_name} requested to join '{$forum->title}'.",
            ['forum_id' => $forum->id, 'user_id' => $user->id]
        );

        return ApiResponse::success($membership, 'Join request submitted. You will be notified after review.');
    }

    public function leave(Forum $forum)
    {
        $user = $this->authUser();
        $membership = ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return ApiResponse::error('You are not a member of this forum.', [], 404);
        }

        if ($membership->role === 'creator') {
            return ApiResponse::error('Forum creator cannot leave the forum.', [], 422);
        }

        $membership->update(['status' => 'removed']);

        return ApiResponse::success([], 'You left the forum.');
    }

    public function members(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->canViewForum($forum, $user)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $members = ForumMembership::with('user:id,first_name,last_name,email')
            ->where('forum_id', $forum->id)
            ->where('status', 'active')
            ->orderByRaw("FIELD(role, 'creator', 'moderator', 'member')")
            ->latest()
            ->get();

        return ApiResponse::success($members);
    }

    public function invite(Request $request, Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isModeratorOrCreator($forum, $user)) {
            return ApiResponse::error('Only creator/moderators can invite members.', [], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $invitedUser = User::find($data['user_id']);
        if (!$invitedUser) {
            return ApiResponse::error('User not found.', [], 404);
        }

        $alreadyMember = ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $invitedUser->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyMember) {
            return ApiResponse::error('User is already a forum member.', [], 409);
        }

        $invitation = ForumInvitation::updateOrCreate(
            [
                'forum_id' => $forum->id,
                'invited_user_id' => $invitedUser->id,
            ],
            [
                'invited_by' => $user->id,
                'status' => 'pending',
                'responded_at' => null,
            ]
        );

        $this->createNotification(
            $invitedUser->id,
            $forum->id,
            'forum_invite',
            'Forum invitation',
            "{$user->first_name} invited you to join '{$forum->title}'.",
            ['invitation_id' => $invitation->id],
            true
        );

        return ApiResponse::success($invitation, 'Invitation sent successfully.');
    }

    public function myInvitations()
    {
        $user = $this->authUser();
        $invitations = ForumInvitation::with(['forum:id,title,type,category', 'inviter:id,first_name,last_name'])
            ->where('invited_user_id', $user->id)
            ->latest()
            ->get();

        return ApiResponse::success($invitations, 'Invitations fetched successfully.');
    }

    public function respondInvitation(Request $request, ForumInvitation $invitation)
    {
        $user = $this->authUser();
        if ((int) $invitation->invited_user_id !== (int) $user->id) {
            return ApiResponse::error('You cannot respond to this invitation.', [], 403);
        }

        $data = $request->validate([
            'action' => 'required|in:accept,decline',
        ]);

        if ($invitation->status !== 'pending') {
            return ApiResponse::error('Invitation has already been responded to.', [], 422);
        }

        if ($data['action'] === 'accept') {
            DB::transaction(function () use ($invitation, $user) {
                $invitation->update([
                    'status' => 'accepted',
                    'responded_at' => now(),
                ]);

                ForumMembership::updateOrCreate(
                    ['forum_id' => $invitation->forum_id, 'user_id' => $user->id],
                    ['role' => 'member', 'status' => 'active', 'joined_at' => now()]
                );
            });

            return ApiResponse::success([], 'Invitation accepted.');
        }

        $invitation->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);

        return ApiResponse::success([], 'Invitation declined.');
    }

    public function removeMember(Forum $forum, User $member)
    {
        $user = $this->authUser();
        if (!$this->isModeratorOrCreator($forum, $user)) {
            return ApiResponse::error('Only creator/moderators can remove members.', [], 403);
        }

        $membership = ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $member->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return ApiResponse::error('Member not found in forum.', [], 404);
        }

        if ($membership->role === 'creator') {
            return ApiResponse::error('Forum creator cannot be removed.', [], 422);
        }

        $membership->update(['status' => 'removed']);

        return ApiResponse::success([], 'Member removed successfully.');
    }

    public function assignModerator(Forum $forum, User $member)
    {
        $user = $this->authUser();
        if (!$this->isCreator($forum, $user)) {
            return ApiResponse::error('Only creator can assign moderators.', [], 403);
        }

        $membership = ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $member->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return ApiResponse::error('Member is not active in this forum.', [], 404);
        }

        if ($membership->role === 'creator') {
            return ApiResponse::error('Creator role cannot be changed.', [], 422);
        }

        $membership->update([
            'role' => $membership->role === 'moderator' ? 'member' : 'moderator',
        ]);

        return ApiResponse::success($membership, 'Member role updated.');
    }

    public function threads(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->canViewForum($forum, $user)) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $threads = ForumThread::with(['user:id,first_name,last_name'])
            ->withCount(['posts' => fn ($query) => $query->where('is_blocked', false)])
            ->where('forum_id', $forum->id)
            ->where('is_blocked', false)
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(30);

        return ApiResponse::success($threads, 'Threads fetched successfully.');
    }

    public function createThread(Request $request, Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->canWriteInForum($forum, $user)) {
            return ApiResponse::error('You cannot create threads in this forum.', [], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $issue = $this->moderationCheck(trim(($data['title'] ?? '') . ' ' . ($data['content'] ?? '')));
        if ($issue) {
            $thread = ForumThread::create([
                'forum_id' => $forum->id,
                'user_id' => $user->id,
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'is_blocked' => true,
                'blocked_reason' => $issue,
                'blocked_at' => now(),
            ]);

            $this->notifyAdmins(
                'Blocked forum thread',
                "{$user->first_name} {$user->last_name}'s thread was blocked in '{$forum->title}'. Reason: {$issue}",
                ['forum_id' => $forum->id, 'thread_id' => $thread->id, 'user_id' => $user->id, 'reason' => $issue]
            );

            return ApiResponse::error("Your thread was blocked: {$issue}", ['reason' => $issue], 422);
        }

        $thread = ForumThread::create([
            'forum_id' => $forum->id,
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
        ]);

        $recipientIds = ForumMembership::where('forum_id', $forum->id)
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id');

        foreach ($recipientIds as $recipientId) {
            $this->createNotification(
                (int) $recipientId,
                $forum->id,
                'new_thread',
                'New thread in forum',
                "{$user->first_name} posted: {$thread->title}",
                ['thread_id' => $thread->id]
            );
        }

        return ApiResponse::success($thread->load('user:id,first_name,last_name'), 'Thread created successfully.');
    }

    public function updateThread(Request $request, Forum $forum, ForumThread $thread)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id) {
            return ApiResponse::error('Thread does not belong to this forum.', [], 422);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        $canModerate = $this->isModeratorOrCreator($forum, $user);
        if (!$canModerate && (int) $thread->user_id !== (int) $user->id) {
            return ApiResponse::error('You cannot edit this thread.', [], 403);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $issue = $this->moderationCheck(trim(($data['title'] ?? $thread->title) . ' ' . ($data['content'] ?? $thread->content)));
        if ($issue) {
            $thread->update([
                'is_blocked' => true,
                'blocked_reason' => $issue,
                'blocked_at' => now(),
            ]);

            $this->notifyAdmins(
                'Blocked forum thread update',
                "{$user->first_name} {$user->last_name}'s thread update was blocked in '{$forum->title}'. Reason: {$issue}",
                ['forum_id' => $forum->id, 'thread_id' => $thread->id, 'user_id' => $user->id, 'reason' => $issue]
            );

            return ApiResponse::error("Your thread update was blocked: {$issue}", ['reason' => $issue], 422);
        }

        $thread->update($data);

        return ApiResponse::success($thread, 'Thread updated.');
    }

    public function deleteThread(Forum $forum, ForumThread $thread)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id) {
            return ApiResponse::error('Thread does not belong to this forum.', [], 422);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        $canModerate = $this->isModeratorOrCreator($forum, $user);
        if (!$canModerate && (int) $thread->user_id !== (int) $user->id) {
            return ApiResponse::error('You cannot delete this thread.', [], 403);
        }

        $thread->delete();

        return ApiResponse::success([], 'Thread deleted.');
    }

    public function pinThread(Forum $forum, ForumThread $thread)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id) {
            return ApiResponse::error('Thread does not belong to this forum.', [], 422);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        if (!$this->isModeratorOrCreator($forum, $user)) {
            return ApiResponse::error('Only creator/moderator can pin threads.', [], 403);
        }

        $thread->update([
            'is_pinned' => !$thread->is_pinned,
            'pinned_at' => !$thread->is_pinned ? now() : null,
        ]);

        return ApiResponse::success($thread, 'Thread pin status updated.');
    }

    public function posts(Forum $forum, ForumThread $thread)
    {
        $user = $this->authUser();
        if (!$this->canViewForum($forum, $user) || (int) $thread->forum_id !== (int) $forum->id) {
            return ApiResponse::error('Access denied.', [], 403);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        $posts = ForumPost::with([
            'user:id,first_name,last_name',
            'quote' => fn ($query) => $query->select(['id', 'user_id', 'content'])->where('is_blocked', false),
            'quote.user:id,first_name,last_name',
            'reactions',
            'replies' => function ($query) {
                $query->with([
                    'user:id,first_name,last_name',
                    'reactions',
                ])->withCount([
                    'reactions as likes_count' => fn ($q) => $q->where('reaction', 'like'),
                    'reactions as dislikes_count' => fn ($q) => $q->where('reaction', 'dislike'),
                ])->where('is_blocked', false);
            },
        ])
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('reaction', 'like'),
                'reactions as dislikes_count' => fn ($q) => $q->where('reaction', 'dislike'),
            ])
            ->where('forum_thread_id', $thread->id)
            ->whereNull('parent_post_id')
            ->where('is_blocked', false)
            ->latest()
            ->paginate(40);

        return ApiResponse::success($posts, 'Posts fetched successfully.');
    }

    public function createPost(Request $request, Forum $forum, ForumThread $thread)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id || !$this->canWriteInForum($forum, $user)) {
            return ApiResponse::error('You cannot post in this thread.', [], 403);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
            'parent_post_id' => 'nullable|exists:forum_posts,id',
            'quote_post_id' => 'nullable|exists:forum_posts,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $issue = $this->moderationCheck($data['content']);
        if ($issue) {
            $post = ForumPost::create([
                'forum_thread_id' => $thread->id,
                'forum_id' => $forum->id,
                'user_id' => $user->id,
                'parent_post_id' => $data['parent_post_id'] ?? null,
                'quote_post_id' => $data['quote_post_id'] ?? null,
                'content' => $data['content'],
                'attachment_path' => null,
                'is_blocked' => true,
                'blocked_reason' => $issue,
                'blocked_at' => now(),
            ]);

            $this->notifyAdmins(
                'Blocked forum post',
                "{$user->first_name} {$user->last_name}'s post was blocked in '{$forum->title}'. Reason: {$issue}",
                ['forum_id' => $forum->id, 'thread_id' => $thread->id, 'post_id' => $post->id, 'user_id' => $user->id, 'reason' => $issue]
            );

            return ApiResponse::error("Your post was blocked: {$issue}", ['reason' => $issue], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = '/storage/' . $request->file('attachment')->store('forum_attachments', 'public');
        }

        $post = ForumPost::create([
            'forum_thread_id' => $thread->id,
            'forum_id' => $forum->id,
            'user_id' => $user->id,
            'parent_post_id' => $data['parent_post_id'] ?? null,
            'quote_post_id' => $data['quote_post_id'] ?? null,
            'content' => $data['content'],
            'attachment_path' => $attachmentPath,
        ]);

        if (!empty($data['parent_post_id'])) {
            $parent = ForumPost::find($data['parent_post_id']);
            if ($parent && (int) $parent->user_id !== (int) $user->id) {
                $this->createNotification(
                    (int) $parent->user_id,
                    $forum->id,
                    'reply',
                    'New reply to your post',
                    "{$user->first_name} replied to your post in '{$forum->title}'.",
                    ['thread_id' => $thread->id, 'post_id' => $post->id]
                );
            }
        }

        return ApiResponse::success($post->load('user:id,first_name,last_name'), 'Post created successfully.');
    }

    public function updatePost(Request $request, Forum $forum, ForumThread $thread, ForumPost $post)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id || (int) $post->forum_thread_id !== (int) $thread->id) {
            return ApiResponse::error('Post does not belong to this thread/forum.', [], 422);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        $canModerate = $this->isModeratorOrCreator($forum, $user);
        $isOwner = (int) $post->user_id === (int) $user->id;
        $editableWindow = $post->created_at && $post->created_at->diffInMinutes(now()) <= 15;

        if (!$canModerate && !($isOwner && $editableWindow)) {
            return ApiResponse::error('You can only edit your post within 15 minutes.', [], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
        ]);

        $issue = $this->moderationCheck($data['content']);
        if ($issue) {
            $post->update([
                'is_blocked' => true,
                'blocked_reason' => $issue,
                'blocked_at' => now(),
            ]);

            $this->notifyAdmins(
                'Blocked forum post update',
                "{$user->first_name} {$user->last_name}'s post update was blocked in '{$forum->title}'. Reason: {$issue}",
                ['forum_id' => $forum->id, 'thread_id' => $thread->id, 'post_id' => $post->id, 'user_id' => $user->id, 'reason' => $issue]
            );

            return ApiResponse::error("Your post was blocked: {$issue}", ['reason' => $issue], 422);
        }

        $post->update([
            'content' => $data['content'],
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
        ]);

        return ApiResponse::success($post, 'Post updated successfully.');
    }

    public function deletePost(Forum $forum, ForumThread $thread, ForumPost $post)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id || (int) $post->forum_thread_id !== (int) $thread->id) {
            return ApiResponse::error('Post does not belong to this thread/forum.', [], 422);
        }
        if ($thread->is_blocked) {
            return ApiResponse::error('This thread is not available.', [], 403);
        }

        $canModerate = $this->isModeratorOrCreator($forum, $user);
        $isOwner = (int) $post->user_id === (int) $user->id;
        $editableWindow = $post->created_at && $post->created_at->diffInMinutes(now()) <= 15;

        if (!$canModerate && !($isOwner && $editableWindow)) {
            return ApiResponse::error('You can only delete your post within 15 minutes.', [], 403);
        }

        $post->delete();

        return ApiResponse::success([], 'Post deleted.');
    }

    public function reactPost(Request $request, ForumPost $post)
    {
        $user = $this->authUser();
        if ($post->is_blocked) {
            return ApiResponse::error('This post is not available.', [], 403);
        }
        if (!$this->canWriteInForum($post->forum, $user)) {
            return ApiResponse::error('You cannot react in this forum.', [], 403);
        }

        $data = $request->validate([
            'reaction' => 'required|in:like,dislike',
        ]);

        $reaction = ForumReaction::updateOrCreate(
            ['forum_post_id' => $post->id, 'user_id' => $user->id],
            ['reaction' => $data['reaction']]
        );

        return ApiResponse::success($reaction, 'Reaction saved.');
    }

    public function reportPost(Request $request, ForumPost $post)
    {
        $user = $this->authUser();
        if ($post->is_blocked) {
            return ApiResponse::error('This post is not available.', [], 403);
        }
        if (!$this->canViewForum($post->forum, $user)) {
            return ApiResponse::error('You cannot report this post.', [], 403);
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ]);

        $report = ForumReport::firstOrCreate(
            ['forum_post_id' => $post->id, 'reported_by' => $user->id],
            [
                'reason' => $data['reason'] ?? null,
                'details' => $data['details'] ?? null,
                'status' => 'open',
            ]
        );

        return ApiResponse::success($report, 'Post reported successfully.');
    }

    public function analytics(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->isModeratorOrCreator($forum, $user)) {
            return ApiResponse::error('Only creator/moderator can view analytics.', [], 403);
        }

        $data = [
            'members_count' => ForumMembership::where('forum_id', $forum->id)->where('status', 'active')->count(),
            'threads_count' => ForumThread::where('forum_id', $forum->id)->where('is_blocked', false)->count(),
            'posts_count' => ForumPost::where('forum_id', $forum->id)->where('is_blocked', false)->count(),
            'reports_open_count' => ForumReport::whereHas('post', fn ($query) => $query->where('forum_id', $forum->id))
                ->where('status', 'open')->count(),
        ];

        return ApiResponse::success($data, 'Forum analytics fetched.');
    }

    public function notifications()
    {
        $user = $this->authUser();
        $notifications = ForumNotification::where('user_id', $user->id)->latest()->paginate(25);
        $unread = ForumNotification::where('user_id', $user->id)->where('is_read', false)->count();

        return ApiResponse::success([
            'notifications' => $notifications,
            'unread_count' => $unread,
        ]);
    }

    public function markNotificationRead(ForumNotification $notification)
    {
        $user = $this->authUser();
        if ((int) $notification->user_id !== (int) $user->id) {
            return ApiResponse::error('Access denied.', [], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return ApiResponse::success($notification, 'Notification marked as read.');
    }

    public function notificationPreferences(Request $request)
    {
        $user = $this->authUser();
        $prefs = $this->forumPrefs($user);

        return ApiResponse::success($prefs, 'Forum notification preferences fetched.');
    }

    public function updateNotificationPreferences(Request $request)
    {
        $user = $this->authUser();

        $data = $request->validate([
            'mentions_only' => 'nullable|boolean',
            'new_threads' => 'nullable|boolean',
            'replies' => 'nullable|boolean',
            'invites' => 'nullable|boolean',
            'announcements' => 'nullable|boolean',
            'email_immediate' => 'nullable|boolean',
        ]);

        $prefs = $this->forumPrefs($user);
        $prefs = array_merge($prefs, $data);

        $allPrefs = $user->preferences ?? [];
        $allPrefs['forum_notifications'] = $prefs;
        $user->preferences = $allPrefs;
        $user->save();

        return ApiResponse::success($prefs, 'Forum notification preferences updated.');
    }

    private function authUser(): User
    {
        return Auth::guard('sanctum')->user();
    }

    private function canViewForum(Forum $forum, User $user): bool
    {
        return ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function canWriteInForum(Forum $forum, User $user): bool
    {
        if ($forum->status !== 'open') {
            return false;
        }

        return ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function isCreator(Forum $forum, User $user): bool
    {
        return ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('role', 'creator')
            ->exists();
    }

    private function isModeratorOrCreator(Forum $forum, User $user): bool
    {
        return ForumMembership::where('forum_id', $forum->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['creator', 'moderator'])
            ->exists();
    }

    private function createNotification(
        int $userId,
        ?int $forumId,
        string $type,
        string $title,
        ?string $body = null,
        array $data = [],
        bool $emailOverride = false
    ): void {
        $notification = ForumNotification::create([
            'user_id' => $userId,
            'forum_id' => $forumId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $user = User::find($userId);
        if (!$user || !$user->email) {
            return;
        }

        $prefs = $this->forumPrefs($user);
        $shouldEmail = $emailOverride || (bool) ($prefs['email_immediate'] ?? true);

        if (!$shouldEmail) {
            return;
        }

        try {
            Mail::raw(
                ($body ? $body . "\n\n" : '') . "Notification: {$title}",
                function ($mail) use ($user, $title) {
                    $mail->to($user->email)->subject("WGRCFP Forum - {$title}");
                }
            );
        } catch (\Throwable $e) {
            // Keep notification delivery non-blocking.
        }
    }

    private function forumPrefs(User $user): array
    {
        $all = $user->preferences ?? [];
        $prefs = $all['forum_notifications'] ?? [];

        return array_merge([
            'mentions_only' => false,
            'new_threads' => true,
            'replies' => true,
            'invites' => true,
            'announcements' => true,
            'email_immediate' => true,
        ], is_array($prefs) ? $prefs : []);
    }

    private function moderationCheck(string $text): ?string
    {
        $clean = trim(strip_tags($text));
        $banned = ForumBannedWord::where('is_active', true)->pluck('word')->all();
        if (empty($banned)) {
            $banned = config('forum_moderation.banned_words', []);
        }

        foreach ($banned as $word) {
            $word = trim((string) $word);
            if ($word === '') {
                continue;
            }

            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $clean)) {
                return "Prohibited language detected";
            }
        }

        $minChars = (int) config('forum_moderation.min_chars', 12);
        $minWords = (int) config('forum_moderation.min_words', 3);

        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = is_array($words) ? count($words) : 0;

        $length = function_exists('mb_strlen') ? mb_strlen($clean) : strlen($clean);
        if ($clean === '' || $length < $minChars || $wordCount < $minWords) {
            return 'Inadequate content';
        }

        return null;
    }

    private function notifyAdmins(string $title, string $body, array $data = []): void
    {
        $admins = Admin::query()
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            if (!$admin->hasPermission('forums')) {
                continue;
            }

            if (!$admin->email) {
                continue;
            }

            try {
                Mail::raw(
                    ($body ? $body . "\n\n" : '') . "Notification: {$title}",
                    function ($mail) use ($admin, $title) {
                        $mail->to($admin->email)->subject("WGRCFP Forum - {$title}");
                    }
                );
            } catch (\Throwable $e) {
                // Keep notification delivery non-blocking.
            }
        }
    }
}
