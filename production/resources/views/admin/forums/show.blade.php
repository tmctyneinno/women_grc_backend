@extends('admin.layouts.app')

@section('title', 'Forum Details')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Forum Details</h1>
        <a href="{{ route('admin.forums.index') }}" class="btn btn-alt-secondary">Back to Forums</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content">
                    <h4 class="fs-5 mb-3">Forum Info</h4>
                    <p class="mb-2"><strong>Title:</strong> {{ $forum->title }}</p>
                    <p class="mb-2"><strong>Category:</strong> {{ $forum->category ?? 'General' }}</p>
                    <p class="mb-2"><strong>Type:</strong> {{ ucfirst($forum->type) }}</p>
                    <p class="mb-2"><strong>Status:</strong> {{ ucfirst($forum->status) }}</p>
                    <p class="mb-2"><strong>Members:</strong> {{ $forum->members_count }}</p>
                    <p class="mb-2"><strong>Threads:</strong> {{ $forum->threads_count }}</p>
                    <p class="mb-2"><strong>Posts:</strong> {{ $forum->posts_count }}</p>
                    <p class="mb-2"><strong>Region Based:</strong> {{ $forum->region_based ? 'Yes' : 'No' }}</p>
                    <p class="mb-2"><strong>Region:</strong> {{ $forum->region ?? '-' }}</p>
                    <p class="mb-0"><strong>Description:</strong><br>{{ $forum->description ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content">
                    <h4 class="fs-5 mb-3">Creator Details</h4>
                    <p class="mb-2"><strong>Name:</strong> {{ $forum->creator?->first_name }} {{ $forum->creator?->last_name }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $forum->creator?->email }}</p>
                    <p class="mb-2"><strong>Status:</strong> {{ ucfirst($forum->creator?->status ?? 'unknown') }}</p>
                    <p class="mb-0"><strong>Created At:</strong> {{ optional($forum->creator?->created_at)->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content">
                    <h4 class="fs-5 mb-3">Actions</h4>
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

                    <form method="POST" action="{{ route('admin.forums.destroy', $forum) }}" onsubmit="return confirm('Delete this forum permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger w-100">Delete Forum</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title">Members</h3>
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
                                    <td>{{ $membership->user?->email }}</td>
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
                    <h3 class="block-title">Recent Threads</h3>
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
</div>
@endsection

