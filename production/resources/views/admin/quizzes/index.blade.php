@extends('admin.layouts.app')

@section('title', 'Quizzes')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('courses.update'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Quiz Questions</h3>
            <p class="text-muted mb-0">{{ $course->title }} / {{ $module->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.modules.index', $course) }}" class="btn btn-alt-secondary">Back to Modules</a>
            @if($canUpdate)
                <a href="{{ route('admin.courses.modules.quizzes.create', [$course, $module]) }}" class="btn btn-primary">Add Quiz Question</a>
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
                        <th>Question</th>
                        <th>Type</th>
                        <th>Pass Mark</th>
                        <th>Max Attempts</th>
                        @if($canUpdate)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($quizzes as $quiz)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $quiz->question }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $quiz->question_type)) }}</td>
                        <td>{{ $quiz->passing_threshold }}%</td>
                        <td>{{ $quiz->max_attempts }}</td>
                        @if($canUpdate)
                            <td class="text-center">
                                <a href="{{ route('admin.courses.modules.quizzes.edit', [$course, $module, $quiz]) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.courses.modules.quizzes.destroy', [$course, $module, $quiz]) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this quiz question?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-alt-danger">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canUpdate ? 6 : 5 }}" class="text-center text-muted">No quiz questions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $quizzes->links() }}
        </div>
    </div>
</div>
@endsection
