@extends('admin.layouts.app')

@section('title', 'Events Management')

@section('content')

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Events Management
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Manage all your events from one place.
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Events
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">
    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <!-- Events Table -->
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">All Events</h3>
                    <div class="block-options">
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus me-1"></i> Create Event
                        </a>
                    </div>
                </div>
                <div class="block-content">
                    <table class="table table-hover table-vcenter">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Event Title</th>
                                <th class="d-none d-sm-table-cell" style="width: 15%;">Type</th>
                                <th class="d-none d-sm-table-cell" style="width: 15%;">Status</th>
                                <th class="d-none d-sm-table-cell" style="width: 15%;">Visibility</th>
                                <th class="d-none d-sm-table-cell" style="width: 15%;">Date</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                            <tr>
                                <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                <td class="fw-semibold fs-sm">
                                    <a href="#">{{ $event->title }}</a>
                                    <div class="text-muted fs-xs mt-1">
                                        {!! Str::limit($event->short_description, 50) !!}
                                    </div>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill 
                                        @if($event->type == 'conference') bg-info-light text-info
                                        @elseif($event->type == 'workshop') bg-warning-light text-warning
                                        @elseif($event->type == 'seminar') bg-success-light text-success
                                        @elseif($event->type == 'networking') bg-primary-light text-primary
                                        @else bg-secondary-light text-secondary @endif">
                                        {{ ucfirst($event->type) }}
                                    </span>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill 
                                        @if($event->status == 'published') bg-success-light text-success
                                        @elseif($event->status == 'draft') bg-warning-light text-warning
                                        @elseif($event->status == 'cancelled') bg-danger-light text-danger
                                        @elseif($event->status == 'completed') bg-info-light text-info
                                        @else bg-secondary-light text-secondary @endif">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill 
                                        @if($event->visibility == 'public') bg-success-light text-success
                                        @elseif($event->visibility == 'private') bg-warning-light text-warning
                                        @elseif($event->visibility == 'members_only') bg-info-light text-info
                                        @else bg-secondary-light text-secondary @endif">
                                        {{ str_replace('_', ' ', ucfirst($event->visibility)) }}
                                    </span>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <div class="fs-sm text-muted">
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}
                                        @if($event->end_date && $event->end_date != $event->start_date)
                                            - {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.events.edit', $event->id) }}" 
                                           class="btn btn-sm btn-alt-secondary" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Event">
                                            <i class="fa fa-fw fa-pencil-alt"></i>
                                        </a>
                                        <!-- Add Speakers Button -->
                                        <a href="{{ route('admin.events.speakers.index', $event->id) }}" 
                                        class="btn btn-sm btn-alt-info" 
                                        data-bs-toggle="tooltip" 
                                        title="Manage Speakers">
                                            <i class="fa fa-fw fa-users"></i>
                                        </a>
                                        <form action="{{ route('admin.events.destroy', $event->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-alt-secondary" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Delete Event">
                                                <i class="fa fa-fw fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-calendar-times fa-2x mb-3"></i>
                                        <h5>No events found</h5>
                                        <p>Get started by creating your first event.</p>
                                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                            <i class="fa fa-plus me-1"></i> Create Event
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    @if($events->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $events->links() }}
                    </div>
                    @endif
                </div>
            </div>
            <!-- END Events Table -->
        </div>
    </div>
</div>
<!-- END Page Content -->

@endsection

@push('scripts')
<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush