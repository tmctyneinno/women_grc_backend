@extends('admin.layouts.app')

@section('content')

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Speakers for: {{ $event->title }}</h1>
        <div>
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back to Events
            </a>
            <a href="{{ route('admin.events.speakers.create', $event) }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Add Speaker
            </a>
        </div>
    </div>

    @php
        // Ensure we have a speakers collection
        $speakers = $speakers ?? $event->speakers ?? collect();
    @endphp

    @if($speakers->isEmpty())
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i>
            No speakers added yet. 
            <a href="{{ route('admin.events.speakers.create', $event) }}" class="alert-link">
                Add your first speaker
            </a>
        </div>
    @else
        <div class="alert alert-success">
            <i class="fa fa-check-circle me-2"></i>
            Showing {{ $speakers->count() }} speaker(s)
        </div>
        
        <div class="row">
            @foreach($speakers as $speaker)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top bg-body-light d-flex align-items-center justify-content-center" 
                             style="height: 200px; overflow: hidden;">
                            @if($speaker->image && file_exists(storage_path('app/public/speakers/' . $speaker->image)))
                                <img src="{{ asset('storage/speakers/' . $speaker->image) }}" 
                                     class="img-fluid h-100 w-100" 
                                     alt="{{ $speaker->name }}"
                                     style="object-fit: cover;">
                            @else
                                <div class="text-center">
                                    <i class="fa fa-user fa-4x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $speaker->name }}</h5>
                            @if($speaker->title)
                                <h6 class="card-subtitle mb-2 text-muted">{{ $speaker->title }}</h6>
                            @endif
                            @if($speaker->brief)
                                <p class="card-text small">{{ Str::limit($speaker->brief, 80) }}</p>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="{{ route('admin.events.speakers.edit', [$event, $speaker]) }}" 
                                   class="btn btn-sm btn-alt-warning">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.events.speakers.destroy', [$event, $speaker]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this speaker?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-danger">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection