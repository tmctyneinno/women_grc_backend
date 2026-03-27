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
                <p class="fs-sm text-bold my-0"><b>{{ $stats['total'] ?? $users->count() }}</b> users registered</p>

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
    @php($admin = auth('admin')->user())
    @php($isSuperAdmin = $admin && $admin->isSuperAdmin())

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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Total Users</div>
                            <div class="fs-4 fw-semibold">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-body-light p-2">
                            <i class="fa fa-users text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Verified</div>
                            <div class="fs-4 fw-semibold text-success">{{ $stats['verified'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-success-light p-2">
                            <i class="fa fa-check text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Pending</div>
                            <div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-warning-light p-2">
                            <i class="fa fa-hourglass-half text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Blocked</div>
                            <div class="fs-4 fw-semibold text-danger">{{ $stats['blocked'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-danger-light p-2">
                            <i class="fa fa-ban text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">All Users</h3>

            <div class="block-options">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm {{ request()->routeIs('admin.users.index') ? 'btn-primary' : 'btn-alt-secondary' }}">
                    All
                    <span class="badge bg-white text-dark ms-1">{{ $stats['total'] ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.users.pend') }}" class="btn btn-sm {{ request()->routeIs('admin.users.pend') ? 'btn-warning' : 'btn-alt-warning' }}">
                    Pending
                    <span class="badge bg-white text-dark ms-1">{{ $stats['pending'] ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.users.blocked') }}" class="btn btn-sm {{ request()->routeIs('admin.users.blocked') ? 'btn-danger' : 'btn-alt-danger' }}">
                    Blocked
                    <span class="badge bg-white text-dark ms-1">{{ $stats['blocked'] ?? 0 }}</span>
                </a>
            </div>
        </div>

        <div class="block-content table-responsive-md">
            <table class="table table-hover table-striped table-vcenter table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="d-none d-lg-table-cell">LinkedIn</th>
                        <th class="d-none d-md-table-cell">Verified</th>
                        <th class="d-none d-lg-table-cell">Joined</th>
                        @if($isSuperAdmin)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>

                @forelse($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <img 
                                src="{{ $user->profile_picture_url ?: asset('storage/profile_pictures/avatar.png') }}" 
                                class="img-avatar img-avatar48"
                                background-color="#f0f0f0"
                                alt="Profile"
                                onerror="this.src='{{ asset('storage/profile_pictures/avatar.png') }}'"
                            >
                        </td>

                        <td class="fw-semibold">
                            {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '-' }}
                            <div class="fs-xs text-muted">
                                {{ $user->job_title ?? 'No job title' }}
                                {{ $user->company ? ' @ ' . $user->company : '' }}
                            </div>
                        </td>

                        <td class="text-truncate" style="max-width: 220px;">
                            <span class="fw-semibold">{{ $user->email ?? 'N/A' }}</span>
                        </td>

                        <td>
                            <span class="badge 
                                {{ $user->status === 'verified' ? 'bg-success' : 
                                   ($user->status === 'pending' ? 'bg-warning' : 
                                   ($user->status === 'blocked' ? 'bg-danger' : 'bg-secondary')) }}">
                                {{ ucfirst($user->status ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            @if($user->linkedin_profile)
                                <a href="{{ $user->linkedin_profile}}" target="_blank" class="text-muted">
                                    <i class="fa fa-link"></i> View Profile
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>

                        <td class="d-none d-md-table-cell">
                            <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-secondary' }}">
                                {{ $user->email_verified_at ? 'Yes' : 'No' }}
                            </span>
                        </td>

                        <td class="fs-sm text-muted d-none d-lg-table-cell">
                            {{ optional($user->created_at)->format('M d, Y') ?? '-' }}
                        </td>

                        @if($isSuperAdmin)
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('admin.users.profile', $user) }}" class="btn btn-sm btn-alt-primary mx-1" data-bs-toggle="tooltip" title="View Profile">
                                    <i class="fa fa-user"></i>
                                </a>

                                {{-- Approve --}}
                                @if($user->status === 'pending')
                                <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-alt-success mx-1" data-bs-toggle="tooltip" title="Approve">
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
                                    <button class="btn btn-sm btn-alt-danger mx-1" data-bs-toggle="tooltip" title="Block">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 9 : 8 }}" class="text-center py-4 text-muted">
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
