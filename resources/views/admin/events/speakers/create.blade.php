@extends('admin.layouts.app')

@section('title', 'Add Speaker')

@section('content')

<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Add Speaker to: {{ $event->title }}
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Add a new speaker to this event
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.events.index') }}">Events</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.events.speakers.index', $event) }}">Speakers</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Add
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="content">
    <!-- Form Errors Alert -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">Please fix the following errors:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Speaker Form -->
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Speaker Information</h3>
        </div>
        <div class="block-content block-content-full">
            <form action="{{ route('admin.events.speakers.store', $event) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                
                <!-- Basic Information -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Speaker details and photo
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <!-- Name -->
                        <div class="mb-4">
                            <label class="form-label" for="name">Speaker Name *</label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                placeholder="Enter speaker's full name"
                                required
                            />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-4">
                            <label class="form-label" for="title">Title / Position</label>
                            <input 
                                type="text" 
                                class="form-control @error('title') is-invalid @enderror" 
                                id="title" 
                                name="title" 
                                value="{{ old('title') }}"
                                placeholder="e.g., CEO, Senior Developer, Professor"
                            />
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Brief -->
                        <div class="mb-4">
                            <label class="form-label" for="brief">Brief Description</label>
                            <textarea 
                                class="form-control @error('brief') is-invalid @enderror" 
                                id="brief"
                                name="brief" 
                                rows="4"
                                placeholder="Brief bio or description of the speaker..."
                            >{{ old('brief') }}</textarea>
                            @error('brief')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum 500 characters</div>
                        </div>

                        <!-- Order -->
                        <div class="mb-4">
                            <label class="form-label" for="order">Display Order</label>
                            <input 
                                type="number" 
                                class="form-control @error('order') is-invalid @enderror" 
                                id="order" 
                                name="order" 
                                value="{{ old('order', 0) }}"
                                min="0"
                                placeholder="Order in which speaker appears"
                            />
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Lower numbers appear first</div>
                        </div>
                    </div>
                </div>

                <!-- Speaker Image -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Speaker photo
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <div class="mb-4">
                            <label class="form-label" for="image">Speaker Photo</label>
                            <input 
                                type="file" 
                                class="form-control @error('image') is-invalid @enderror" 
                                id="image" 
                                name="image"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            />
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Accepted formats: JPEG, PNG, JPG, GIF, WebP. Maximum size: 2MB
                            </div>
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImage" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row push">
                    <div class="col-lg-4"></div>
                    <div class="col-lg-8 col-xl-8">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.events.speakers.index', $event) }}" class="btn btn-alt-secondary">
                                    Cancel
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Save Speaker
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview functionality
        const imageInput = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    preview.style.display = 'none';
                    previewImage.src = '';
                }
            });
        }

        // Character counter for brief
        const briefTextarea = document.getElementById('brief');
        if (briefTextarea) {
            briefTextarea.addEventListener('input', function() {
                const maxLength = 500;
                const currentLength = this.value.length;
                
                if (currentLength > maxLength) {
                    this.value = this.value.substring(0, maxLength);
                }
            });
        }
    });
</script>
@endpush