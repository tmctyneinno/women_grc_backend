@extends('admin.layouts.app')

@section('title', 'Event Bookings')

@section('content')

<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">
                Bookings for: {{ $event->title }}
            </h3>
            <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-alt-secondary">
                ← Back to Events
            </a>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $booking->user->first_name . ' ' . $booking->user->last_name ?? 'N/A' }}</td>
                            <td>{{ $booking->user->email ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
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