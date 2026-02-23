@extends('admin.layouts.app')

@section('title', 'Create Course')

@section('content')

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Create New Course</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Add a new learning course to the platform.
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
                        Create
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

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

    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Course Details</h3>
        </div>

        <div class="block-content">
            <form action="{{ route('admin.courses.store') }}" method="POST">
                @csrf

                {{-- Title --}}
                <div class="mb-4">
                    <label class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="title" 
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="e.g. Introduction to ESG Compliance"
                           required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description" 
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3"
                              placeholder="Short description of the course">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Objectives --}}
                <div class="mb-4">
                    <label class="form-label">Learning Objectives</label>
                    <textarea name="objectives" 
                              class="form-control @error('objectives') is-invalid @enderror"
                              rows="3"
                              placeholder="What will learners achieve?">{{ old('objectives') }}</textarea>
                    @error('objectives')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <input type="text" 
                           name="category" 
                           class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category') }}"
                           placeholder="e.g. ESG, AML, Data Privacy">
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tags --}}
                <div class="mb-4">
                    <label class="form-label">Tags (comma-separated)</label>
                    <input type="text" 
                           name="tags" 
                           class="form-control @error('tags') is-invalid @enderror"
                           value="{{ old('tags') }}"
                           placeholder="ESG, Compliance, Risk">
                    @error('tags')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Certificate Toggle --}}
                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="has_certificate" value="0">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="has_certificate" 
                           id="has_certificate"
                           value="1"
                           {{ old('has_certificate') ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_certificate">
                        Issue certificate on completion
                    </label>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-alt-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-alt-primary">
                        <i class="fa fa-save me-1"></i> Create Course
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection