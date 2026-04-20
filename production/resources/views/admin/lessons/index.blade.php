@extends('admin.layouts.app')

@section('title', 'Lessons')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Lessons</h3>
            <p class="text-muted mb-0">{{ $course->title }} / {{ $module->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.modules.index', $course) }}" class="btn btn-alt-secondary">Back to Modules</a>
            @if($canUpdate)
                <a href="{{ route('admin.courses.modules.lessons.create', [$course, $module]) }}" class="btn btn-primary">Add Lesson</a>
            @endif
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
                        @if($canUpdate)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($lessons as $lesson)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $lesson->title }}</td>
                        <td>{{ ucfirst($lesson->type) }}</td>
                        <td>{{ $lesson->position }}</td>
                        @if($canUpdate)
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
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canUpdate ? 5 : 4 }}" class="text-center text-muted">No lessons yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $lessons->links() }}
        </div>
    </div>
</div>
@endsection
