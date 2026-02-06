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

    {{-- Debug info --}}
    <div class="alert alert-info mb-4">
        <p><strong>Debug:</strong> Speakers count: {{ $speakers->count() }}</p>
    </div>

    @if($speakers->isEmpty())
        <div class="alert alert-info">
            No speakers added yet. <a href="{{ route('admin.events.speakers.create', $event) }}">Add your first speaker</a>
        </div>
    @else
        <div class="alert alert-success">
            Found {{ $speakers->count() }} speaker(s)
        </div>
        
        <div class="row">
            @foreach($speakers as $speaker)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        @if($speaker->image)
                            @php
                                // Try different image paths
                                $imagePath = 'storage/speakers/' . $speaker->image;
                                $fullPath = storage_path('app/public/speakers/' . $speaker->image);
                            @endphp
                            
                            @if(file_exists($fullPath))
                                <img src="{{ asset($imagePath) }}" 
                                     class="card-img-top" 
                                     alt="{{ $speaker->name }}" 
                                     style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fa fa-user fa-3x text-muted"></i>
                                    <small class="mt-2 text-muted">Image not found</small>
                                </div>
                            @endif
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fa fa-user fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $speaker->name }}</h5>
                            @if($speaker->title)
                                <h6 class="card-subtitle mb-2 text-muted">{{ $speaker->title }}</h6>
                            @endif
                            @if($speaker->brief)
                                <p class="card-text">{{ Str::limit($speaker->brief, 100) }}</p>
                            @endif
                            <p><small class="text-muted">ID: {{ $speaker->id }}</small></p>
                            <div class="btn-group">
                                <a href="{{ route('admin.events.speakers.edit', [$event, $speaker]) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.events.speakers.destroy', [$event, $speaker]) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Delete this speaker?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
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