@extends('admin.layouts.app')

@section('title', 'Create Forum')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('forums.create'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Create Forum</h1>
        <a href="{{ route('admin.forums.index') }}" class="btn btn-alt-danger">Back to Forums</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Something went wrong:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="block block-rounded">
        <div class="block-content">
            <form method="POST" action="{{ route('admin.forums.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="public" {{ old('type') === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ old('type') === 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="e.g. Compliance, Mentorship">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region Based</label>
                        <select name="region_based" class="form-select">
                            <option value="0" {{ old('region_based') === '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('region_based') === '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <select name="region" class="form-select">
                            <option value="">Select timezone</option>
                            @foreach($timezones as $tz)
                                <option value="{{ $tz->timezone }}" {{ old('region') === $tz->timezone ? 'selected' : '' }}>
                                    {{ $tz->timezone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    @if($canCreate)
                        <button type="submit" class="btn btn-primary">Create Forum</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
