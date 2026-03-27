@extends('admin.layouts.app')

@section('title', 'Admins')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Admins</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.admins.activity') }}" class="btn btn-alt-secondary">
                <i class="fa fa-clock me-1"></i> Activity
            </a>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                <i class="fa fa-user-plus me-1"></i> Add Admin
            </a>
        </div>
    </div>

    <div class="block block-rounded">
        <div class="block-content block-content-full">
            <div class="table-responsive">
                <table class="table table-vcenter table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $admin->role)) }}</td>
                                <td>
                                    @php($perms = $admin->permissions ?? [])
                                    @if($admin->isSuperAdmin())
                                        <span class="badge bg-success">All</span>
                                    @elseif(empty($perms))
                                        <span class="text-muted">None</span>
                                    @else
                                        @foreach($perms as $perm)
                                            <span class="badge bg-info text-dark">{{ ucfirst($perm) }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $admin->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                                    @if(!$admin->isSuperAdmin())
                                        <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No admins found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $admins->links() }}
        </div>
    </div>
</div>
@endsection
