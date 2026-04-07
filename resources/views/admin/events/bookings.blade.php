@extends('admin.layouts.app')

@section('title', 'Event Bookings')

@section('content')

<div class="content">
    @php($admin = auth('admin')->user())
    @php($canEmailBookers = $admin && $admin->hasPermission('events.update'))
    @php($canDeleteBooking = $admin && $admin->hasPermission('events.update'))
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">
                Bookings for: {{ $event->title }}
            </h3>
            <div class="d-flex gap-2">
                @if($canEmailBookers)
                    <a href="{{ route('admin.events.bookings.email.form', $event) }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-envelope me-1"></i> Email Bookers
                    </a>
                @endif
                <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-alt-secondary">
                    &larr; Back to Events
                </a>
            </div>
        </div>

        <div class="block-content">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Booked At</th>
                        @if($canDeleteBooking)
                            <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $booking->user->first_name . ' ' . $booking->user->last_name ?? 'N/A' }}</td>
                            <td>
                                {{ $booking->user->email ?? 'N/A' }}
                                @if($canViewUser && $booking->user)
                                    <a href="{{ route('admin.users.profile', $booking->user) }}" class="ms-2 text-muted" title="View User Profile">
                                        <i class="fa fa-user"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>{{ $booking->created_at ? $booking->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                            @if($canDeleteBooking)
                                <td class="text-end">
                                    <form action="{{ route('admin.events.bookings.delete', [$event, $booking]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this booking?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canDeleteBooking ? 6 : 5 }}" class="text-center text-muted">
                                No bookings yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $bookings->links() }}
        </div>
    </div>
</div>

@endsection
