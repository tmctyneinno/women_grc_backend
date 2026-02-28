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

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-alt-secondary">Cancel</a>
                    <button type="submit" class="btn btn-alt-primary">
                        <i class="fa fa-save me-1"></i> Update Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modules Section --}}
    <div class="block block-rounded mt-4">
        <div class="block-header block-header-default">
            <h3 class="block-title">Modules</h3>
            <a href="{{ route('admin.courses.modules.create', $course) }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> Add Module
            </a>
        </div>

        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($course->modules as $module)
                    <tr>
                        <td>{{ $module->title }}</td>
                        <td>{{ $module->order }}</td>
                        <td>
                            <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $module->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
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