@extends('admin.layouts.app')

@section('title', 'All Modules')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">All Modules</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Module</th>
                        <th>Course</th>
                        <th>Order</th>
                        <th>Lessons</th>
                        <th>Quizzes</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($modules as $module)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $module->title }}</td>
                        <td>{{ $module->course?->title }}</td>
                        <td>{{ $module->position }}</td>
                        <td>{{ $module->lessons_count }}</td>
                        <td>{{ $module->quizzes_count }}</td>
                        <td>
                            <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $module->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.courses.modules.edit', [$module->course_id, $module->id]) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                            <a href="{{ route('admin.courses.modules.lessons.index', [$module->course_id, $module->id]) }}" class="btn btn-sm btn-alt-info">Lessons</a>
                            <a href="{{ route('admin.courses.modules.quizzes.index', [$module->course_id, $module->id]) }}" class="btn btn-sm btn-alt-warning">Quizzes</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No modules found.</td></tr>
                @endforelse
                </tbody>
            </table>

            {{ $modules->links() }}
        </div>
    </div>
</div>
@endsection

