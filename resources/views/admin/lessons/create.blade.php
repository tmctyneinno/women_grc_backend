@extends('admin.layouts.app')

@section('title', 'Create Lesson')

@section('content')
<div class="content">
    <h3 class="mb-3">Create Lesson - {{ $module->title }}</h3>

    <form action="{{ route('admin.courses.modules.lessons.store', [$course, $module]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Text</option>
                <option value="video" {{ old('type') === 'video' ? 'selected' : '' }}>Video</option>
                <option value="file" {{ old('type') === 'file' ? 'selected' : '' }}>File</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Content (text or video URL)</label>
            <textarea name="content" class="form-control" rows="4">{{ old('content') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">File Path (for downloadable resource)</label>
            <input type="text" name="file_path" class="form-control" value="{{ old('file_path') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Upload File (optional)</label>
            <input type="file" name="uploaded_file" class="form-control">
            <div class="form-text">If provided, uploaded file URL will be saved automatically.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Position</label>
            <input type="number" min="1" name="position" class="form-control" value="{{ old('position', 1) }}">
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.modules.lessons.index', [$course, $module]) }}" class="btn btn-alt-secondary">Cancel</a>
            <button class="btn btn-primary">Create Lesson</button>
        </div>
    </form>
</div>
@endsection
