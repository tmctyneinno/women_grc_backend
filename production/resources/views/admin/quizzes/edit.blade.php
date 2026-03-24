@extends('admin.layouts.app')

@section('title', 'Edit Quiz Question')

@section('content')
<div class="content">
    <h3 class="mb-3">Edit Quiz Question - {{ $module->title }}</h3>

    <form action="{{ route('admin.courses.modules.quizzes.update', [$course, $module, $quiz]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Question</label>
            <textarea name="question" class="form-control" rows="3" required>{{ old('question', $quiz->question) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Question Type</label>
            <select name="question_type" class="form-select" required>
                <option value="multiple_choice" {{ old('question_type', $quiz->question_type) === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                <option value="true_false" {{ old('question_type', $quiz->question_type) === 'true_false' ? 'selected' : '' }}>True/False</option>
                <option value="short_answer" {{ old('question_type', $quiz->question_type) === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Options (comma separated)</label>
            <input type="text"
                   name="options"
                   class="form-control"
                   value="{{ old('options', is_array($quiz->options) ? implode(', ', $quiz->options) : '') }}"
                   placeholder="Option A, Option B, Option C">
        </div>

        <div class="mb-3">
            <label class="form-label">Correct Answer</label>
            <input type="text" name="correct_answer" class="form-control" value="{{ old('correct_answer', $quiz->correct_answer) }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Passing Threshold (%)</label>
                <input type="number" min="1" max="100" name="passing_threshold" class="form-control" value="{{ old('passing_threshold', $quiz->passing_threshold) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Max Attempts</label>
                <input type="number" min="1" max="10" name="max_attempts" class="form-control" value="{{ old('max_attempts', $quiz->max_attempts) }}" required>
            </div>
        </div>

        <div class="mb-3 form-check form-switch">
            <input type="hidden" name="show_feedback" value="0">
            <input class="form-check-input" type="checkbox" name="show_feedback" value="1" {{ old('show_feedback', $quiz->show_feedback) ? 'checked' : '' }}>
            <label class="form-check-label">Show immediate feedback</label>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.modules.quizzes.index', [$course, $module]) }}" class="btn btn-alt-secondary">Cancel</a>
            <button class="btn btn-primary">Update Question</button>
        </div>
    </form>
</div>
@endsection

