@extends('admin.layouts.app')

@section('title', 'User Profile')

@section('content')
@php($admin = auth('admin')->user())
@if(!$admin || !$admin->isSuperAdmin())
    <div class="content">
        <div class="alert alert-danger">You do not have permission to view this page.</div>
    </div>
@else
<div class="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h1 class="h3 mb-1">User Profile</h1>
            <p class="text-muted mb-0">Detailed overview of user activity and account information.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-alt-secondary mt-3 mt-md-0">Back to Users</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content text-center">
                    <img 
                        src="{{ $user->profile_picture_url ?: asset('storage/profile_pictures/avatar.png') }}" 
                        class="img-avatar img-avatar96 mb-3"
                        alt="Profile"
                        onerror="this.src='{{ asset('storage/profile_pictures/avatar.png') }}'"
                    >
                    <h3 class="h5 mb-1">{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '-' }}</h3>
                    <div class="text-muted">{{ $user->email ?? 'N/A' }}</div>
                    <div class="mt-2">
                        <span class="badge 
                            {{ $user->status === 'verified' ? 'bg-success' : 
                               ($user->status === 'pending' ? 'bg-warning' : 
                               ($user->status === 'blocked' ? 'bg-danger' : 'bg-secondary')) }}">
                            {{ ucfirst($user->status ?? 'unknown') }}
                        </span>
                    </div>
                </div>
                <div class="block-content border-top">
                    <div class="mb-2"><strong>Phone:</strong> {{ $user->phone_number ?? 'N/A' }}</div>
                    <div class="mb-2"><strong>Job Title:</strong> {{ $user->job_title ?? 'N/A' }}</div>
                    <div class="mb-2"><strong>Company:</strong> {{ $user->company ?? 'N/A' }}</div>
                    <div class="mb-2"><strong>LinkedIn:</strong>
                        @if($user->linkedin_profile)
                            <a href="{{ $user->linkedin_profile }}" target="_blank" rel="noopener">View Profile</a>
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="mb-2"><strong>Joined:</strong> {{ optional($user->created_at)->format('M d, Y') ?? '-' }}</div>
                    <div class="mb-2"><strong>Last Login:</strong> {{ optional($user->last_login_at)->format('M d, Y H:i') ?? '-' }}</div>
                    <div class="mb-2"><strong>Last Login IP:</strong> {{ $user->last_login_ip ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-12">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h3 class="block-title">Recent Event Bookings</h3>
                        </div>
                        <div class="block-content">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Status</th>
                                            <th>Booked At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentBookings as $booking)
                                            <tr>
                                                <td>{{ $booking->event?->title ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($booking->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">No event bookings.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h3 class="block-title">Recent Course Enrollments</h3>
                        </div>
                        <div class="block-content">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Status</th>
                                            <th>Enrolled At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentEnrollments as $enrollment)
                                            <tr>
                                                <td>{{ $enrollment->course?->title ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($enrollment->status ?? '-') }}</td>
                                                <td>{{ optional($enrollment->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">No course enrollments.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h3 class="block-title">Recent Transactions</h3>
                        </div>
                        <div class="block-content">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Paid At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions as $tx)
                                            <tr>
                                                <td>{{ $tx->reference }}</td>
                                                <td>{{ strtoupper($tx->status) }}</td>
                                                <td>{{ $tx->currency }} {{ number_format((float) $tx->total_amount, 2) }}</td>
                                                <td>{{ optional($tx->paid_at)->format('M d, Y H:i') ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No transactions.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h3 class="block-title">Memberships</h3>
                        </div>
                        <div class="block-content">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Membership</th>
                                            <th>Tier</th>
                                            <th>Status</th>
                                            <th>Started</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->memberships as $membership)
                                            <tr>
                                                <td>{{ $membership->membership?->name ?? 'N/A' }}</td>
                                                <td>{{ $membership->tier?->name ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($membership->status ?? '-') }}</td>
                                                <td>{{ optional($membership->started_at)->format('M d, Y') ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No memberships.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h3 class="block-title">Forum Activity</h3>
                        </div>
                        <div class="block-content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="fw-semibold mb-2">Memberships</div>
                                    <ul class="list-group">
                                        @forelse($recentForumMemberships as $membership)
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>{{ $membership->forum?->title ?? 'N/A' }}</span>
                                                <span class="text-muted fs-sm">{{ ucfirst($membership->status ?? '-') }}</span>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">No forum memberships.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-semibold mb-2">Recent Threads</div>
                                    <ul class="list-group">
                                        @forelse($recentForumThreads as $thread)
                                            <li class="list-group-item">
                                                {{ $thread->title ?? 'Untitled' }}
                                                <div class="text-muted fs-sm">{{ $thread->forum?->title ?? 'N/A' }}</div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">No forum threads.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="col-12">
                                    <div class="fw-semibold mb-2">Recent Posts</div>
                                    <ul class="list-group">
                                        @forelse($recentForumPosts as $post)
                                            <li class="list-group-item">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 120) }}
                                                <div class="text-muted fs-sm">Thread: {{ $post->thread?->title ?? 'N/A' }}</div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">No forum posts.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endif
@endsection
