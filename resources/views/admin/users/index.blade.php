@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('content')

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Users Management</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Manage all registered users.
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Users
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

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
        <div class="block-header block-header-default">
            <h3 class="block-title">All Users</h3>

            <div class="block-options">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-alt-secondary">All</a>
                <a href="{{ route('admin.users.pend') }}" class="btn btn-sm btn-alt-warning">Pending</a>
                <a href="{{ route('admin.users.blocked') }}" class="btn btn-sm btn-alt-danger">Blocked</a>
            </div>
        </div>

        <div class="block-content table-responsive">
            <table class="table table-hover table-vcenter">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>

                @forelse($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <img 
                                src="{{ $user->profile_picture_url ?: asset('images/default-avatar.png') }}" 
                                class="img-avatar img-avatar48"
                                alt="Profile"
                                onerror="this.src='{{ asset('images/default-avatar.png') }}'"
                            >
                        </td>

                        <td class="fw-semibold">
                            {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '—' }}
                            <div class="fs-xs text-muted">
                                {{ $user->job_title ?? 'No job title' }}
                                {{ $user->company ? ' @ ' . $user->company : '' }}
                            </div>
                        </td>

                        <td>{{ $user->email ?? 'N/A' }}</td>

                        <td>
                            <span class="badge 
                                {{ $user->status === 'active' ? 'bg-success' : 
                                   ($user->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                {{ ucfirst($user->status ?? 'unknown') }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-secondary' }}">
                                {{ $user->email_verified_at ? 'Yes' : 'No' }}
                            </span>
                        </td>

                        <td class="fs-sm text-muted">
                            {{ optional($user->created_at)->format('M d, Y') ?? '—' }}
                        </td>

                        <td class="text-center">
                            <div class="btn-group">

                                {{-- Approve --}}
                                @if($user->status === 'pending')
                                <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-alt-success" data-bs-toggle="tooltip" title="Approve">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Block --}}
                                @if($user->status !== 'blocked')
                                <form action="{{ route('admin.users.block', $user->id) }}" method="POST"
                                      onsubmit="return confirm('Block this user?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-alt-danger" data-bs-toggle="tooltip" title="Block">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fa fa-users fa-2x mb-3"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>

            @if($users->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
