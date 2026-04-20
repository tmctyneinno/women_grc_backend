@extends('admin.layouts.app')

@section('title', 'Membership Plans')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('memberships.create'))
    @php($canUpdate = $admin && $admin->hasPermission('memberships.update'))
    @php($canDelete = $admin && $admin->hasPermission('memberships.delete'))
    @php($canSeeActions = $admin && ($canUpdate || $canDelete))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Membership Plans</h3>
            @if($canCreate)
                <a href="{{ route('admin.membership-plans.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Create Plan
                </a>
            @endif
        </div>

        <div class="block-content table-responsive">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Tiers</th>
                        <th>Updated</th>
                        @if($canSeeActions)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($memberships as $membership)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $membership->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($membership->description, 80) }}</td>
                        <td>{{ $membership->tiers_count }}</td>
                        <td>{{ optional($membership->updated_at)->format('Y-m-d') }}</td>
                        @if($canSeeActions)
                            <td class="text-center">
                                @if($canUpdate)
                                    <a href="{{ route('admin.membership-plans.tiers.index', $membership) }}" class="btn btn-sm btn-alt-primary" title="Manage Tiers">
                                        <i class="fa fa-layer-group"></i>
                                    </a>
                                    <a href="{{ route('admin.membership-plans.edit', $membership) }}" class="btn btn-sm btn-alt-secondary">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                @endif
                                @if($canDelete)
                                    <form method="POST" class="d-inline" action="{{ route('admin.membership-plans.destroy', $membership) }}" onsubmit="return confirm('Delete this membership plan?')">
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
                        <td colspan="{{ $canSeeActions ? 6 : 5 }}" class="text-center text-muted py-4">No membership plans yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $memberships->links() }}
        </div>
    </div>
</div>
@endsection
