@extends('admin.layouts.app')

@section('title', 'Edit Article')

@section('content')
@php
    $adminUser = auth('admin')->user();
    $isSuperAdmin = $adminUser && method_exists($adminUser, 'isSuperAdmin')
        ? $adminUser->isSuperAdmin()
        : ($adminUser && strtolower($adminUser->email) === 'enquiries@wgrcfp.org');
@endphp
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Edit Article</h3>
            <a href="{{ route('admin.articles.index') }}" class="btn btn-alt-secondary">Back</a>
        </div>
        <div class="block-content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $article->title) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Summary</label>
                    <textarea name="summary" class="form-control" rows="3">{{ old('summary', $article->summary) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="8" required>{{ old('content', $article->content) }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tag</label>
                        <input type="text" name="tag" class="form-control" value="{{ old('tag', $article->tag) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        @if($isSuperAdmin)
                            <select name="status" class="form-select" required>
                                @foreach(['draft','pending','published','rejected'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $article->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="status" value="pending">
                            <div class="form-control-plaintext text-muted">Pending (requires super admin approval)</div>
                        @endif
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    @if($article->cover_image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $article->cover_image) }}" alt="cover" style="max-width: 180px; border-radius: 8px;">
                        </div>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-alt-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
