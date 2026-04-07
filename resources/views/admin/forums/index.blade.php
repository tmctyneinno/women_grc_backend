@extends('admin.layouts.app')

@section('title', 'Forums')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('forums.create'))
    @php($canModerate = $admin && $admin->hasPermission('forums.moderate'))
    @php($canView = $admin && $admin->hasPermission('forums.view'))
    @php($canUpdate = $admin && $admin->hasPermission('forums.update'))
    @php($canDelete = $admin && $admin->hasPermission('forums.delete'))
    @php($canSeeActions = $admin && ($canView || $canUpdate || $canDelete))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">All Forums</h3>
            <div class="block-options">
                @if($canModerate)
                    <a href="{{ route('admin.forums.banned-words.index') }}" class="btn btn-sm btn-alt-secondary">
                        <i class="fa fa-ban"></i> Banned Words
                    </a>
                @endif
                @if($canCreate)
                    <a href="{{ route('admin.forums.create') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> Create Forum
                    </a>
                @endif
            </div>
        </div>

        <div class="block-content">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search title/category/description">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (['open', 'closed', 'archived'] as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>

        <div class="block-content table-responsive">
            <table class="table table-hover table-vcenter">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Forum</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Creator</th>
                        <th>Members</th>
                        <th>Threads</th>
                        <th>Posts</th>
                        @if($canSeeActions)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($forums as $forum)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $forum->title }}</div>
                            <div class="small text-muted">{{ $forum->category ?? 'General' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $forum->type === 'public' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($forum->type) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge
                                {{ $forum->status === 'open' ? 'bg-success' : ($forum->status === 'closed' ? 'bg-warning' : 'bg-secondary') }}">
                                {{ ucfirst($forum->status) }}
                            </span>
                        </td>
                        <td>
                            <div>{{ $forum->creator?->name ?? '-' }}</div>
                            <div class="small text-muted">{{ $forum->creator?->email ?? '-' }}</div>
                        </td>
                        <td>{{ $forum->members_count }}</td>
                        <td>{{ $forum->threads_count }}</td>
                        <td>{{ $forum->posts_count }}</td>
                        @if($canSeeActions)
                            <td class="text-center">
                                @if($canView)
                                    <a href="{{ route('admin.forums.show', $forum) }}" class="btn btn-sm btn-alt-info" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif

                                @if($canUpdate)
                                    @if($forum->status !== 'archived')
                                        <form method="POST" action="{{ route('admin.forums.deactivate', $forum) }}" class="d-inline" onsubmit="return confirm('Deactivate this forum?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-alt-warning" title="Deactivate">
                                                <i class="fa fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.forums.activate', $forum) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-alt-success" title="Activate">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if($canDelete)
                                    <form method="POST" action="{{ route('admin.forums.destroy', $forum) }}" class="d-inline" onsubmit="return confirm('Delete this forum permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-alt-danger" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canSeeActions ? 9 : 8 }}" class="text-center text-muted py-4">No forums found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $forums->links() }}
        </div>
    </div>
</div>
@endsection
