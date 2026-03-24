@extends('admin.layouts.app')

@section('title', 'Create Module')

@section('content')

<div class="content">
    <h3>Create Module for "{{ $course->title }}"</h3>

    <form method="POST" action="{{ route('admin.courses.modules.store', $course) }}">
        @csrf

        <div class="mb-3">
            <label>Module Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Order</label>
            <input type="number" name="position" class="form-control" value="1">
        </div>

        <div class="form-check mb-3">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <label class="form-check-label">Active</label>
        </div>

        <div class="form-check mb-3">
            <input type="hidden" name="require_quiz_to_unlock" value="0">
            <input class="form-check-input" type="checkbox" name="require_quiz_to_unlock" value="1">
            <label class="form-check-label">Require quiz pass to unlock next module</label>
        </div>

        <button class="btn btn-primary">Create Module</button>
    </form>
</div>

@endsection
