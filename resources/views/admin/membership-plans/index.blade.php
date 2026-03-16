@extends('admin.layouts.app')

@section('title', 'Membership Plans')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Membership Plans</h3>
            <a href="{{ route('admin.membership-plans.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Create Plan
            </a>
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
                        <th class="text-center">Actions</th>
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
                        <td class="text-center">
                            <a href="{{ route('admin.membership-plans.tiers.index', $membership) }}" class="btn btn-sm btn-alt-primary" title="Manage Tiers">
                                <i class="fa fa-layer-group"></i>
                            </a>
                            <a href="{{ route('admin.membership-plans.edit', $membership) }}" class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-pencil-alt"></i>
                            </a>
                            <form method="POST" class="d-inline" action="{{ route('admin.membership-plans.destroy', $membership) }}" onsubmit="return confirm('Delete this membership plan?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-alt-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No membership plans yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $memberships->links() }}
        </div>
    </div>
</div>
@endsection
