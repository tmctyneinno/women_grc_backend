@extends('admin.layouts.app')

@section('title', 'Forum Details')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('forums.update'))
    @php($canDelete = $admin && $admin->hasPermission('forums.delete'))
    @php($canModerate = $admin && $admin->hasPermission('forums.moderate'))
    @php($canInvite = $admin && $admin->hasPermission('forums.invite'))
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-danger">Forum Details</h1>
        <a href="{{ route('admin.forums.index') }}" class="btn btn-alt-danger">Back to Forums</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content">
                    <h4 class="fs-5 mb-3 text-primary">Forum Info</h4>
                    <p class="mb-2"><strong>Title:</strong> {{ $forum->title }}</p>
                    <p class="mb-2"><strong>Category:</strong> {{ $forum->category ?? 'General' }}</p>
                    <p class="mb-2"><strong>Type:</strong> {{ ucfirst($forum->type) }}</p>
                    <p class="mb-2"><strong>Status:</strong> {{ ucfirst($forum->status) }}</p>
                    <p class="mb-2"><strong>Members:</strong> {{ $forum->members_count }}</p>
                    <p class="mb-2"><strong>Threads:</strong> {{ $forum->threads_count }}</p>
                    <p class="mb-2"><strong>Posts:</strong> {{ $forum->posts_count }}</p>
                    <p class="mb-2"><strong>Blocked Threads:</strong> {{ $blockedThreadsCount ?? 0 }}</p>
                    <p class="mb-2"><strong>Blocked Posts:</strong> {{ $blockedPostsCount ?? 0 }}</p>
                    <p class="mb-2"><strong>Region Based:</strong> {{ $forum->region_based ? 'Yes' : 'No' }}</p>
                    <p class="mb-2"><strong>Region:</strong> {{ $forum->region ?? '-' }}</p>
                    <p class="mb-0"><strong>Description:</strong><br>{{ $forum->description ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <h4 class="fs-5 mb-3 text-primary">Creator Details</h4>
                    <p class="mb-2"><strong>Name:</strong> {{ $forum->creator?->name ?? '-' }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $forum->creator?->email ?? '-' }}</p>
                    <p class="mb-2"><strong>Role:</strong> {{ ucfirst($forum->creator?->role ?? 'admin') }}</p>
                    <p class="mb-2"><strong>Status:</strong> {{ $forum->creator?->is_active ? 'Active' : 'Inactive' }}</p>
                    <p class="mb-0"><strong>Created At:</strong> {{ optional($forum->creator?->created_at)->format('Y-m-d H:i:s') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content">
                    <h4 class="fs-5 mb-3 text-primary">Actions</h4>
                    @if($canUpdate)
                        @if($forum->status !== 'archived')
                            <form method="POST" action="{{ route('admin.forums.deactivate', $forum) }}" class="mb-2" onsubmit="return confirm('Deactivate this forum?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-warning w-100">Deactivate Forum</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.forums.activate', $forum) }}" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-success w-100">Reactivate Forum</button>
                            </form>
                        @endif
                    @endif

                    @if($canDelete)
                        <form method="POST" action="{{ route('admin.forums.destroy', $forum) }}" onsubmit="return confirm('Delete this forum permanently?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger w-100">Delete Forum</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-primary">Members</h3>
                </div>
                <div class="block-content table-responsive">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($forum->memberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->first_name }} {{ $membership->user?->last_name }}</td>
                                    <td>
                                        {{ $membership->user?->email }}
                                        @if($canViewUser && $membership->user)
                                            <a href="{{ route('admin.users.profile', $membership->user) }}" class="ms-2 text-muted" title="View User Profile">
                                                <i class="fa fa-user"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary">{{ ucfirst($membership->role) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted text-center">No members.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-primary">Recent Threads</h3>
                </div>
                <div class="block-content table-responsive">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($forum->threads as $thread)
                                <tr>
                                    <td>{{ $thread->title }}</td>
                                    <td>{{ $thread->user?->first_name }} {{ $thread->user?->last_name }}</td>
                                    <td>{{ optional($thread->created_at)->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted text-center">No threads.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-primary">Pending Join Requests</h3>
                </div>
                <div class="block-content table-responsive">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                @if($canModerate)
                                    <th class="text-center">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingMemberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->first_name }} {{ $membership->user?->last_name }}</td>
                                    <td>{{ $membership->user?->email }}</td>
                                    @if($canModerate)
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('admin.forums.memberships.approve', [$forum, $membership]) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-alt-success" title="Approve">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.forums.memberships.reject', [$forum, $membership]) }}" class="d-inline" onsubmit="return confirm('Reject this request?')">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-alt-danger" title="Reject">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canModerate ? 3 : 2 }}" class="text-muted text-center">No pending requests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-primary">Invite User (Private Forum)</h3>
                </div>
                <div class="block-content">
                    @if($canInvite)
                        <form method="POST" action="{{ route('admin.forums.invites.send', $forum) }}" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">User Email</label>
                                <input type="email" name="email" class="form-control" placeholder="user@example.com" required>
                                <div class="form-text">An invite link will be emailed to the user.</div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary">Send Invite</button>
                            </div>
                        </form>
                    @endif

                    <div class="mt-4">
                        <div class="fw-semibold mb-2">Pending Invites</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingInvites as $invite)
                                        <tr>
                                            <td>{{ $invite->invitedUser?->email ?? '-' }}</td>
                                            <td><span class="badge bg-warning">Pending</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-muted text-center">No pending invites.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-primary">Blocked Threads</h3>
                </div>
                <div class="block-content table-responsive">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Reason</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blockedThreads as $thread)
                                <tr>
                                    <td>{{ $thread->title }}</td>
                                    <td>{{ $thread->user?->first_name }} {{ $thread->user?->last_name }}</td>
                                    <td>{{ $thread->blocked_reason ?? 'Blocked' }}</td>
                                    <td>{{ optional($thread->blocked_at)->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center">No blocked threads.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-primary">Blocked Posts</h3>
                </div>
                <div class="block-content table-responsive">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Excerpt</th>
                                <th>Author</th>
                                <th>Thread</th>
                                <th>Reason</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blockedPosts as $post)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 60) }}</td>
                                    <td>{{ $post->user?->first_name }} {{ $post->user?->last_name }}</td>
                                    <td>{{ $post->thread?->title ?? '-' }}</td>
                                    <td>{{ $post->blocked_reason ?? 'Blocked' }}</td>
                                    <td>{{ optional($post->blocked_at)->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center">No blocked posts.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
