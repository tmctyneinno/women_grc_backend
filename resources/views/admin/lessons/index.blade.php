@extends('admin.layouts.app')

@section('title', 'Lessons')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Lessons</h3>
            <p class="text-muted mb-0">{{ $course->title }} / {{ $module->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.modules.index', $course) }}" class="btn btn-alt-secondary">Back to Modules</a>
            <a href="{{ route('admin.courses.modules.lessons.create', [$course, $module]) }}" class="btn btn-primary">Add Lesson</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="block block-rounded">
        <div class="block-content table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Position</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($lessons as $lesson)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $lesson->title }}</td>
                        <td>{{ ucfirst($lesson->type) }}</td>
                        <td>{{ $lesson->position }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.courses.modules.lessons.edit', [$course, $module, $lesson]) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                            <form method="POST"
                                  action="{{ route('admin.courses.modules.lessons.destroy', [$course, $module, $lesson]) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this lesson?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-alt-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No lessons yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $lessons->links() }}
        </div>
    </div>
</div>
@endsection

