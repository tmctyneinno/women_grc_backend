@extends('admin.layouts.app')

@section('title','Podcasts')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('podcasts.create'))
    @php($canUpdate = $admin && $admin->hasPermission('podcasts.update'))
    @php($canDelete = $admin && $admin->hasPermission('podcasts.delete'))
    @php($canSeeActions = $admin && ($canUpdate || $canDelete))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Podcasts</h3>
            @if($canCreate)
                <a href="{{ route('admin.podcasts.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Podcast
                </a>
            @endif
        </div>

        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Tag</th>
                        <th>Status</th>
                        <th>Contributors</th>
                        @if($canSeeActions)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($podcasts as $podcast)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($podcast->cover_url)
                                <img src="{{ $podcast->cover_url }}" alt="cover" width="44" height="44" style="object-fit:cover;border-radius:6px;">
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $podcast->title }}</td>
                        <td>{{ $podcast->tag ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $podcast->status === 'published' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($podcast->status) }}
                            </span>
                        </td>
                        <td>{{ $podcast->contributors_count }}</td>
                        @if($canSeeActions)
                            <td class="text-center">
                                @if($canUpdate)
                                    <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="btn btn-sm btn-alt-secondary">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                @endif
                                @if($canDelete)
                                    <form method="POST" class="d-inline" action="{{ route('admin.podcasts.destroy', $podcast) }}"
                                          onsubmit="return confirm('Delete this podcast?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-alt-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canSeeActions ? 7 : 6 }}" class="text-center text-muted py-4">No podcasts yet</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $podcasts->links() }}
        </div>
    </div>
</div>
@endsection
