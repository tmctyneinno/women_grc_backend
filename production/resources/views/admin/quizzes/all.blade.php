@extends('admin.layouts.app')

@section('title', 'All Quiz')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">All Quiz</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Type</th>
                        <th>Module</th>
                        <th>Course</th>
                        <th>Pass Mark</th>
                        <th>Attempts</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($quizzes as $quiz)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $quiz->question }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $quiz->question_type)) }}</td>
                        <td>{{ $quiz->module?->title }}</td>
                        <td>{{ $quiz->module?->course?->title }}</td>
                        <td>{{ $quiz->passing_threshold }}%</td>
                        <td>{{ $quiz->max_attempts }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.courses.modules.quizzes.edit', [$quiz->module?->course_id, $quiz->module_id, $quiz->id]) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No quiz records found.</td></tr>
                @endforelse
                </tbody>
            </table>

            {{ $quizzes->links() }}
        </div>
    </div>
</div>
@endsection

