@extends('admin.layouts.app')

@section('title', 'Event Waitlist')

@section('content')

<div class="content">
    @php($admin = auth('admin')->user())
    @php($canDeleteBooking = $admin && $admin->hasPermission('events.update'))
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">
                Waitlist for: {{ $event->title }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.events.bookings', $event) }}" class="btn btn-sm btn-alt-secondary">
                    &larr; Back to Bookings
                </a>
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
                        <th>Joined At</th>
                        @if($canDeleteBooking)
                            <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($waitlist as $booking)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $booking->user ? trim(($booking->user->first_name ?? '') . ' ' . ($booking->user->last_name ?? '')) : 'N/A' }}
                            </td>
                            <td>
                                {{ $booking->user->email ?? 'N/A' }}
                                @if($canViewUser && $booking->user)
                                    <a href="{{ route('admin.users.profile', $booking->user) }}" class="ms-2 text-muted" title="View User Profile">
                                        <i class="fa fa-user"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($booking->status === 'confirmed' || $booking->status === 'paid')
                                    <span class="badge bg-success">{{ ucfirst($booking->status) }}</span>
                                @elseif($booking->status === 'waitlisted')
                                    <span class="badge bg-info">{{ ucfirst($booking->status) }}</span>
                                @elseif($booking->status === 'cancelled')
                                    <span class="badge bg-danger">{{ ucfirst($booking->status) }}</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($booking->status) }}</span>
                                @endif
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
                                No waitlisted users yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $waitlist->links() }}
        </div>
    </div>
</div>

@endsection
