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
            ->with(['creator:id,first_name,last_name'])
            ->withCount([
                'memberships as members_count' => fn ($query) => $query->where('status', 'active'),
                'threads',
            ])
            ->when($mine, function ($query) use ($user) {
                $query->whereHas('memberships', function ($memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->id)->where('status', 'active');
                });
            })
            ->when(!$mine, function ($query) use ($user) {
                $query->where(function ($nested) use ($user) {
                    $nested->where('type', 'public')
                        ->orWhereHas('memberships', function ($memberQuery) use ($user) {
                            $memberQuery->where('user_id', $user->id)->where('status', 'active');
                        });
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
        $user = $this->authUser();
        if ($user->status !== 'verified') {
            return ApiResponse::error('Only verified members can create forums.', [], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:120',
            'type' => 'required|in:public,private',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:80',
            'region_based' => 'nullable|boolean',
            'region' => 'nullable|string|max:120',
        ]);

        $forum = DB::transaction(function () use ($data, $user) {
            $forum = Forum::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'type' => $data['type'],
                'tags' => $data['tags'] ?? [],
                'region_based' => (bool) ($data['region_based'] ?? false),
                'region' => $data['region'] ?? null,
                'created_by' => $user->id,
            ]);

            ForumMembership::create([
                'forum_id' => $forum->id,
                'user_id' => $user->id,
                'role' => 'creator',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $forum;
        });

        return ApiResponse::success($forum, 'Forum created successfully.');
    }

    public function show(Forum $forum)
    {
        $user = $this->authUser();
        if (!$this->canViewForum($forum, $user)) {
            return ApiResponse::error('You do not have access to this forum.', [], 403);
        }

        $forum->load([
            'creator:id,first_name,last_name',
            'memberships' => fn ($query) => $query->with('user:id,first_name,last_name')->where('status', 'active'),
            'threads' => fn ($query) => $query->with('user:id,first_name,last_name')->withCount('posts')->orderByDesc('is_pinned')->latest()->limit(30),
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
        if ($forum->type !== 'public') {
            return ApiResponse::error('This is a private forum. Invitation is required.', [], 403);
        }

        $membership = ForumMembership::updateOrCreate(
            ['forum_id' => $forum->id, 'user_id' => $user->id],
            ['status' => 'active', 'joined_at' => now()]
        );

        return ApiResponse::success($membership, 'Joined forum successfully.');
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
            ->withCount('posts')
            ->where('forum_id', $forum->id)
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

        $canModerate = $this->isModeratorOrCreator($forum, $user);
        if (!$canModerate && (int) $thread->user_id !== (int) $user->id) {
            return ApiResponse::error('You cannot edit this thread.', [], 403);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $thread->update($data);

        return ApiResponse::success($thread, 'Thread updated.');
    }

    public function deleteThread(Forum $forum, ForumThread $thread)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id) {
            return ApiResponse::error('Thread does not belong to this forum.', [], 422);
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

        $posts = ForumPost::with([
            'user:id,first_name,last_name',
            'quote:id,user_id,content',
            'quote.user:id,first_name,last_name',
            'reactions',
            'replies' => function ($query) {
                $query->with([
                    'user:id,first_name,last_name',
                    'reactions',
                ])->withCount([
                    'reactions as likes_count' => fn ($q) => $q->where('reaction', 'like'),
                    'reactions as dislikes_count' => fn ($q) => $q->where('reaction', 'dislike'),
                ]);
            },
        ])
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('reaction', 'like'),
                'reactions as dislikes_count' => fn ($q) => $q->where('reaction', 'dislike'),
            ])
            ->where('forum_thread_id', $thread->id)
            ->whereNull('parent_post_id')
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

        $data = $request->validate([
            'content' => 'required|string',
            'parent_post_id' => 'nullable|exists:forum_posts,id',
            'quote_post_id' => 'nullable|exists:forum_posts,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

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

        $canModerate = $this->isModeratorOrCreator($forum, $user);
        $isOwner = (int) $post->user_id === (int) $user->id;
        $editableWindow = $post->created_at && $post->created_at->diffInMinutes(now()) <= 15;

        if (!$canModerate && !($isOwner && $editableWindow)) {
            return ApiResponse::error('You can only edit your post within 15 minutes.', [], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
        ]);

        $post->update(['content' => $data['content']]);

        return ApiResponse::success($post, 'Post updated successfully.');
    }

    public function deletePost(Forum $forum, ForumThread $thread, ForumPost $post)
    {
        $user = $this->authUser();
        if ((int) $thread->forum_id !== (int) $forum->id || (int) $post->forum_thread_id !== (int) $thread->id) {
            return ApiResponse::error('Post does not belong to this thread/forum.', [], 422);
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
            'threads_count' => ForumThread::where('forum_id', $forum->id)->count(),
            'posts_count' => ForumPost::where('forum_id', $forum->id)->count(),
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
        if ($forum->type === 'public') {
            return true;
        }

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
}
