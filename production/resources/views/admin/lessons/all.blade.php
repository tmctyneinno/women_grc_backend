@extends('admin.layouts.app')

@section('title', 'All Lessons')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">All Lessons</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lesson</th>
                        <th>Type</th>
                        <th>Module</th>
                        <th>Course</th>
                        <th>Order</th>
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
                        <td class="text-capitalize">{{ $lesson->type }}</td>
                        <td>{{ $lesson->module?->title }}</td>
                        <td>{{ $lesson->module?->course?->title }}</td>
                        <td>{{ $lesson->position }}</td>
                        @if($canUpdate)
                            <td class="text-center">
                                <a href="{{ route('admin.courses.modules.lessons.edit', [$lesson->module?->course_id, $lesson->module_id, $lesson->id]) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canUpdate ? 7 : 6 }}" class="text-center text-muted">No lessons found.</td></tr>
                @endforelse
                </tbody>
            </table>

            {{ $lessons->links() }}
        </div>
    </div>
</div>
@endsection
