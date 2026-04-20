@extends('admin.layouts.app')

@section('title', 'Edit Module')

@section('content')

<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex justify-content-between align-items-center py-2">
            <div>
                <h1 class="h3 fw-bold mb-1">Edit Module</h1>
                <p class="text-muted mb-0">
                    Course: <strong>{{ $course->title }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.courses.modules.index', $course) }}" class="btn btn-alt-secondary">
                Back to Modules
            </a>
        </div>
    </div>
</div>

<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Module Details</h3>
        </div>

        <div class="block-content">
            <form action="{{ route('admin.courses.modules.update', [$course, $module]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Module Title</label>
                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $module->title) }}"
                           required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $module->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Order</label>
                    <input type="number"
                           name="position"
                           min="1"
                           class="form-control @error('position') is-invalid @enderror"
                           value="{{ old('position', $module->position) }}">
                    @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $module->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="require_quiz_to_unlock" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="require_quiz_to_unlock"
                           value="1"
                           {{ old('require_quiz_to_unlock', $module->require_quiz_to_unlock) ? 'checked' : '' }}>
                    <label class="form-check-label">Require quiz pass to unlock next module</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.courses.modules.index', $course) }}" class="btn btn-alt-secondary">
                        Cancel
                    </a>
                    @if($canUpdate)
                        <button class="btn btn-alt-primary">
                            <i class="fa fa-save me-1"></i> Update Module
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
