@extends('admin.layouts.app')

@section('title', 'Edit Lesson')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))
    <h3 class="mb-3">Edit Lesson - {{ $module->title }}</h3>

    <form action="{{ route('admin.courses.modules.lessons.update', [$course, $module, $lesson]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <option value="text" {{ old('type', $lesson->type) === 'text' ? 'selected' : '' }}>Text</option>
                <option value="video" {{ old('type', $lesson->type) === 'video' ? 'selected' : '' }}>Video</option>
                <option value="file" {{ old('type', $lesson->type) === 'file' ? 'selected' : '' }}>File</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Content (text or video URL)</label>
            <textarea name="content" class="form-control" rows="4">{{ old('content', $lesson->content) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">File Path</label>
            <input type="text" name="file_path" class="form-control" value="{{ old('file_path', $lesson->file_path) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Upload New File (optional)</label>
            <input type="file" name="uploaded_file" class="form-control">
            <div class="form-text">Uploading a file replaces the saved file URL.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Position</label>
            <input type="number" min="1" name="position" class="form-control" value="{{ old('position', $lesson->position) }}">
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.modules.lessons.index', [$course, $module]) }}" class="btn btn-alt-secondary">Cancel</a>
            @if($canUpdate)
                <button class="btn btn-primary">Update Lesson</button>
            @endif
        </div>
    </form>
</div>
@endsection
