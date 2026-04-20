@extends('admin.layouts.app')

@section('title', 'Edit Course')

@section('content')

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Edit Course</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Update course details and manage modules.
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.courses.index') }}">Courses</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Edit
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the errors below.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Course Edit Form --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Course Details</h3>
        </div>

        <div class="block-content">
            <form action="{{ route('admin.courses.update', $course) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div class="mb-4">
                    <label class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $course->title) }}"
                           required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $course->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Objectives --}}
                <div class="mb-4">
                    <label class="form-label">Learning Objectives</label>
                    <textarea name="objectives"
                              rows="3"
                              class="form-control @error('objectives') is-invalid @enderror">{{ old('objectives', $course->objectives) }}</textarea>
                    @error('objectives') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <input type="text"
                           name="category"
                           class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category', $course->category) }}">
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Tags --}}
                <div class="mb-4">
                    <label class="form-label">Tags (comma-separated)</label>
                    <input type="text"
                        name="tags"
                        class="form-control @error('tags') is-invalid @enderror"
                        value="{{ old('tags', is_array($course->tags) ? implode(', ', $course->tags) : $course->tags) }}">
                    @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Status --}}
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $course->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Enrollment Type</label>
                    <select name="enrollment_type" class="form-select">
                        <option value="open" {{ old('enrollment_type', $course->enrollment_type) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="invite_only" {{ old('enrollment_type', $course->enrollment_type) === 'invite_only' ? 'selected' : '' }}>Invite Only</option>
                        <option value="premium" {{ old('enrollment_type', $course->enrollment_type) === 'premium' ? 'selected' : '' }}>Premium</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Navigation Mode</label>
                    <select name="navigation_mode" class="form-select">
                        <option value="free" {{ old('navigation_mode', $course->navigation_mode) === 'free' ? 'selected' : '' }}>Free Navigation</option>
                        <option value="locked" {{ old('navigation_mode', $course->navigation_mode) === 'locked' ? 'selected' : '' }}>Locked Progression</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Pass Threshold (%)</label>
                    <input type="number"
                           min="1"
                           max="100"
                           name="passing_threshold"
                           class="form-control @error('passing_threshold') is-invalid @enderror"
                           value="{{ old('passing_threshold', $course->passing_threshold ?? 70) }}">
                    @error('passing_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Certificate --}}
                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="has_certificate" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="has_certificate"
                           id="has_certificate"
                           value="1"
                           {{ old('has_certificate', $course->has_certificate) ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_certificate">
                        Issue certificate on completion
                    </label>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="requires_quiz_pass" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="requires_quiz_pass"
                           id="requires_quiz_pass"
                           value="1"
                           {{ old('requires_quiz_pass', $course->requires_quiz_pass) ? 'checked' : '' }}>
                    <label class="form-check-label" for="requires_quiz_pass">
                        Require learners to pass quizzes before completion
                    </label>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           id="is_active"
                           value="1"
                           {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="is_paid" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_paid"
                           id="is_paid"
                           value="1"
                           {{ old('is_paid', $course->is_paid ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_paid">
                        Paid Course
                    </label>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Price</label>
                        <input type="number"
                               min="0"
                               step="0.01"
                               name="price"
                               class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price', $course->price ?? 0) }}">
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Currency</label>
                        <input type="text"
                               maxlength="3"
                               name="currency"
                               class="form-control @error('currency') is-invalid @enderror"
                               value="{{ old('currency', $course->currency ?? 'GBP') }}">
                        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-alt-secondary">Cancel</a>
                    @if($canUpdate)
                        <button type="submit" class="btn btn-alt-primary">
                            <i class="fa fa-save me-1"></i> Update Course
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Modules Section --}}
    <div class="block block-rounded mt-4">
        <div class="block-header block-header-default">
            <h3 class="block-title">Modules</h3>
            @if($canUpdate)
                <a href="{{ route('admin.courses.modules.create', $course) }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus"></i> Add Module
                </a>
            @endif
        </div>

        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Status</th>
                        @if($canUpdate)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($course->modules as $module)
                    <tr>
                        <td>{{ $module->title }}</td>
                        <td>{{ $module->position }}</td>
                        <td>
                            <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $module->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        @if($canUpdate)
                            <td class="text-center">
                                <a href="{{ route('admin.courses.modules.edit', [$course, $module]) }}"
                                   class="btn btn-sm btn-alt-secondary">
                                    <i class="fa fa-pencil-alt"></i>
                                </a>

                                <form method="POST" class="d-inline"
                                      action="{{ route('admin.courses.modules.destroy', [$course, $module]) }}"
                                      onsubmit="return confirm('Delete this module?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-alt-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canUpdate ? 4 : 3 }}" class="text-center text-muted py-4">
                            No modules yet
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
