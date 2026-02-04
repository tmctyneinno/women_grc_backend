@extends('admin.layouts.app')

@section('title', 'Edit Event')

@section('content')

<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Edit Event: {{ $event->title }}
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Update the details below for this event
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
                    <li class="breadcrumb-item" aria-current="page">
                        Edit
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="content">
    <!-- Event Form -->
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Event Information</h3>
        </div>
        <div class="block-content block-content-full">
            <form action="{{ route('admin.events.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Basic event details
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <!-- Title -->
                        <div class="mb-4">
                            <label class="form-label" for="title">Event Title *</label>
                            <input 
                                type="text" 
                                class="form-control @error('title') is-invalid @enderror" 
                                id="title" 
                                name="title" 
                                value="{{ old('title', $event->title) }}"
                                placeholder="Enter event title"
                                required
                            />
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <!-- Type -->
                        <div class="mb-4">
                            <label class="form-label" for="type">Event Type *</label>
                            <select 
                                class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type"
                                required
                            >
                                <option value="" disabled>Select event type</option>
                                <option value="conference" {{ old('type', $event->type) == 'conference' ? 'selected' : '' }}>Conference</option>
                                <option value="workshop" {{ old('type', $event->type) == 'workshop' ? 'selected' : '' }}>Workshop</option>
                                <option value="seminar" {{ old('type', $event->type) == 'seminar' ? 'selected' : '' }}>Seminar</option>
                                <option value="meeting" {{ old('type', $event->type) == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="networking" {{ old('type', $event->type) == 'networking' ? 'selected' : '' }}>Networking</option>
                                <option value="other" {{ old('type', $event->type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label" for="status">Status *</label>
                            <select 
                                class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status"
                                required
                            >
                                <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $event->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Visibility -->
                        <div class="mb-4">
                            <label class="form-label" for="visibility">Visibility *</label>
                            <select 
                                class="form-select @error('visibility') is-invalid @enderror" 
                                id="visibility" 
                                name="visibility"
                                required
                            >
                                <option value="public" {{ old('visibility', $event->visibility) == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="private" {{ old('visibility', $event->visibility) == 'private' ? 'selected' : '' }}>Private</option>
                                <option value="members_only" {{ old('visibility', $event->visibility) == 'members_only' ? 'selected' : '' }}>Members Only</option>
                            </select>
                            @error('visibility')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Event schedule
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <!-- Start Date -->
                        <div class="mb-4">
                            <label class="form-label" for="start_date">Start Date *</label>
                            <input 
                                type="date" 
                                class="form-control @error('start_date') is-invalid @enderror" 
                                id="start_date" 
                                name="start_date" 
                                value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}"
                                required
                            />
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="mb-4">
                            <label class="form-label" for="end_date">End Date *</label>
                            <input 
                                type="date" 
                                class="form-control @error('end_date') is-invalid @enderror" 
                                id="end_date" 
                                name="end_date" 
                                value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}"
                                required
                            />
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Time -->
                        <div class="mb-4">
                            <label class="form-label" for="start_time">Start Time *</label>
                            <input 
                                type="time" 
                                class="form-control @error('start_time') is-invalid @enderror" 
                                id="start_time" 
                                name="start_time" 
                                value="{{ old('start_time', $event->start_time ? $event->start_time->format('H:i') : '09:00') }}"
                                required
                            />
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <!-- End Date -->
                        <div class="mb-4">
                            <label class="form-label" for="end_time">End Time *</label>
                            <input 
                                type="time" 
                                class="form-control @error('end_time') is-invalid @enderror" 
                                id="end_time" 
                                name="end_time" 
                                value="{{ old('end_time', $event->end_time ? $event->end_time->format('H:i') : '17:00') }}"
                                required
                            />
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- End Time -->

                    </div>
                </div>

                <!-- Location & Capacity -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Event location and capacity
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <!-- Venue -->
                        <div class="mb-4">
                            <label class="form-label" for="venue">Venue *</label>
                            <input 
                                type="text" 
                                class="form-control @error('venue') is-invalid @enderror" 
                                id="venue" 
                                name="venue" 
                                value="{{ old('venue', $event->venue) }}"
                                placeholder="Enter venue name and address"
                                required
                            />
                            @error('venue')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <!-- Registration Link -->
                        <div class="mb-4">
                            <label class="form-label" for="meeting_link">Registration Link  *</label>
                            <input 
                                type="text" 
                                class="form-control @error('meeting_link') is-invalid @enderror" 
                                id="meeting_link" 
                                name="meeting_link" 
                                value="{{ old('meeting_link', $event->meeting_link) }}"
                                placeholder="Enter Meeting link"
                                required
                            />
                            @error('meeting_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Capacity -->
                        <div class="mb-4">
                            <label class="form-label" for="capacity">Capacity</label>
                            <input 
                                type="number" 
                                class="form-control @error('capacity') is-invalid @enderror" 
                                id="capacity" 
                                name="capacity" 
                                value="{{ old('capacity', $event->capacity) }}"
                                min="1"
                                placeholder="Maximum number of attendees"
                            />
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty for unlimited capacity</div>
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <label class="form-label" for="price">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control @error('price') is-invalid @enderror" 
                                    id="price" 
                                    name="price" 
                                    value="{{ old('price', $event->price) }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter 0 for free events</div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Event description
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <!-- Short Description -->
                        <div class="mb-4">
                            <label class="form-label" for="short_description">Short Description</label>
                            <textarea 
                                class="form-control @error('short_description') is-invalid @enderror" 
                                id="short_description" 
                                name="short_description" 
                                rows="3"
                                placeholder="Brief summary (max 500 characters)"
                            >{{ old('short_description', $event->short_description) }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum 500 characters</div>
                        </div>

                        <!-- Full Description -->
                        <div class="mb-4">
                            <label class="form-label" for="description">Full Description *</label>
                            <textarea 
                                class="form-control @error('description') is-invalid @enderror" 
                                id="js-ckeditor5-classic" 
                                name="description" 
                                rows=""
                                placeholder="Describe your event in detail..."
                                required
                            >{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="row push">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Event image
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-8">
                        <div class="mb-4">
                            <label class="form-label" for="featured_image">Featured Image</label>
                            
                            <!-- Current Image Preview -->
                            @if($event->featured_image)
                            <div class="mb-3">
                                <p class="text-muted small">Current Image:</p>
                                <img src="{{ asset('storage/' . $event->featured_image) }}" 
                                     alt="Current featured image" 
                                     class="img-fluid rounded" 
                                     style="max-height: 200px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label text-danger" for="remove_image">
                                        Remove current image
                                    </label>
                                </div>
                            </div>
                            @endif
                            
                            <!-- New Image Upload -->
                            <input 
                                type="file" 
                                class="form-control @error('featured_image') is-invalid @enderror" 
                                id="featured_image" 
                                name="featured_image"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            />
                            @error('featured_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Accepted formats: JPEG, PNG, JPG, GIF, WebP. Maximum size: 5MB. Leave empty to keep current image.
                            </div>
                            
                            <!-- New Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <p class="text-muted small">New Image Preview:</p>
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
                                <a href="{{ route('admin.events.index') }}" class="btn btn-alt-secondary">
                                    Cancel
                                </a>
                            </div>
                            <div>
                                <button type="submit" name="action" value="draft" class="btn btn-alt-warning me-2">
                                    Save as Draft
                                </button>
                                <button type="submit" name="action" value="update" class="btn btn-primary">
                                    Update Event
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
    // Image preview functionality
    document.getElementById('featured_image').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        
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

    // Update end date min when start date changes
    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        if (this.value > endDate.value) {
            endDate.value = this.value;
        }
        endDate.min = this.value;
    });

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.value || slugField.value === '{{ $event->slug }}') {
            const slug = this.value
                .toLowerCase()
                .replace(/[^\w\s]/gi, '')
                .replace(/\s+/g, '-')
                .trim();
            slugField.value = slug;
        }
    });

    // Character counter for short description
    const shortDesc = document.getElementById('short_description');
    if (shortDesc) {
        shortDesc.addEventListener('input', function() {
            const maxLength = 500;
            const currentLength = this.value.length;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });
    }
</script>
@endpush