@extends('admin.layouts.app')

@section('title', 'Mentors')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('mentors.create'))
    @php($canUpdate = $admin && $admin->hasPermission('mentors.update'))

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Something went wrong:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Mentors</h3>
            @if($canCreate)
                <a href="{{ route('admin.mentors.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Add Mentor
                </a>
            @endif
        </div>

        <div class="block-content table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mentor</th>
                        <th>Domain</th>
                        <th>Location</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mentors as $mentor)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $mentor->user ? trim(($mentor->user->first_name ?? '') . ' ' . ($mentor->user->last_name ?? '')) : 'N/A' }}
                                </div>
                                <div class="fs-xs text-muted">
                                    {{ $mentor->title ?? 'No title' }}
                                </div>
                            </td>
                            <td>{{ $mentor->domain ?? 'N/A' }}</td>
                            <td>{{ trim(($mentor->region ?? '') . ' ' . ($mentor->country ?? '')) ?: 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $mentor->availability_status === 'available' ? 'success' : ($mentor->availability_status === 'busy' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $mentor->availability_status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $mentor->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $mentor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($canUpdate)
                                    <a href="{{ route('admin.mentors.edit', $mentor) }}" class="btn btn-sm btn-alt-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.mentors.toggle', $mentor) }}" method="POST" class="d-inline" onsubmit="return confirm('Change mentor status?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $mentor->is_active ? 'btn-alt-danger' : 'btn-alt-success' }}">
                                            <i class="fa {{ $mentor->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No mentors yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($mentors->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $mentors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
