@extends('admin.layouts.app')

@section('title', 'Modules')

@section('content')

<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))
    <div class="d-flex justify-content-between mb-3">
        <h3>Modules for: {{ $course->title }}</h3>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-alt-secondary">Back to Courses</a>
        @if($canUpdate)
            <a href="{{ route('admin.courses.modules.create', $course) }}" class="btn btn-primary">
                Add Module
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Order</th>
                <th>Lessons</th>
                <th>Quizzes</th>
                <th>Status</th>
                @if($canUpdate)
                    <th class="text-center">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse($modules as $module)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $module->title }}</td>
                <td>{{ $module->position }}</td>
                <td>{{ $module->lessons_count }}</td>
                <td>{{ $module->quizzes_count }}</td>
                <td>
                    <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                @if($canUpdate)
                    <td class="text-center">
                        <a href="{{ route('admin.courses.modules.edit', [$course, $module]) }}" class="btn btn-sm btn-alt-primary">
                            Edit
                        </a>
                        <a href="{{ route('admin.courses.modules.lessons.index', [$course, $module]) }}" class="btn btn-sm btn-alt-info">
                            Lessons
                        </a>
                        <a href="{{ route('admin.courses.modules.quizzes.index', [$course, $module]) }}" class="btn btn-sm btn-alt-warning">
                            Quizzes
                        </a>

                        <form action="{{ route('admin.courses.modules.destroy', [$course, $module]) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this module?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-alt-danger">Delete</button>
                        </form>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $canUpdate ? 7 : 6 }}" class="text-center text-muted">No modules yet</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $modules->links() }}
</div>

@endsection
