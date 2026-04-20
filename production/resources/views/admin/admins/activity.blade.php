@extends('admin.layouts.app')

@section('title', 'Admin Activity')

@section('content')
<div class="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h1 class="h3 mb-1">Admin Activity</h1>
            <p class="text-muted mb-0">Track admin logins and actions across the platform.</p>
        </div>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-alt-secondary mt-3 mt-md-0">Back to Admins</a>
    </div>

    <div class="block block-rounded">
        <div class="block-content block-content-full">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select name="admin_id" class="form-select">
                        <option value="">All Admins</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ (string) $adminId === (string) $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }} ({{ $admin->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="q" class="form-control" placeholder="Search..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Target</th>
                            <th>IP</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $log->admin?->name }}</div>
                                    <div class="text-muted fs-sm">{{ $log->admin?->email }}</div>
                                </td>
                                <td><span class="badge bg-info text-dark">{{ $log->action }}</span></td>
                                <td>{{ $log->description ?? '-' }}</td>
                                <td>
                                    @if($log->target_type)
                                        <span class="text-muted">{{ class_basename($log->target_type) }} #{{ $log->target_id }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $log->ip_address ?? '-' }}</td>
                                <td>{{ $log->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
