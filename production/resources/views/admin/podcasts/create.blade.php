@extends('admin.layouts.app')

@section('title','Add Podcast')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('podcasts.create'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Create Podcast</h3>
            <a href="{{ route('admin.podcasts.index') }}" class="btn btn-alt-secondary">Back</a>
        </div>

        <div class="block-content">
            <form method="POST" action="{{ route('admin.podcasts.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tag</label>
                        <input type="text" name="tag" class="form-control" value="{{ old('tag') }}" placeholder="Leadership">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" class="form-control" value="{{ old('duration') }}" placeholder="18:42">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                            <option value="published" @selected(old('status') === 'published')>Published</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Summary</label>
                        <textarea name="summary" class="form-control" rows="2">{{ old('summary') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Audio File</label>
                        <input type="file" name="audio_file" class="form-control" required>
                        <div class="form-text">MP3, WAV, M4A, OGG, WEBM</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cover Image</label>
                        <input type="file" name="cover_image" class="form-control">
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Contributors</h5>
                    @if($canCreate)
                        <button type="button" class="btn btn-sm btn-alt-primary" id="addContributor">
                            <i class="fa fa-plus"></i> Add Contributor
                        </button>
                    @endif
                </div>

                <div id="contributorsWrapper" class="d-grid gap-3">
                    <div class="row g-2 contributor-row">
                        <div class="col-md-4">
                            <input type="text" name="contributor_name[]" class="form-control" placeholder="Name">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="contributor_role[]" class="form-control" placeholder="Role / Title">
                        </div>
                        <div class="col-md-3">
                            <input type="file" name="contributor_photo[]" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-alt-danger remove-contributor">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    @if($canCreate)
                        <button class="btn btn-primary">Save Podcast</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('contributorsWrapper');
    const addBtn = document.getElementById('addContributor');

    addBtn?.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 contributor-row';
        row.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="contributor_name[]" class="form-control" placeholder="Name">
            </div>
            <div class="col-md-4">
                <input type="text" name="contributor_role[]" class="form-control" placeholder="Role / Title">
            </div>
            <div class="col-md-3">
                <input type="file" name="contributor_photo[]" class="form-control">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-alt-danger remove-contributor">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        wrapper.appendChild(row);
    });

    wrapper?.addEventListener('click', function (event) {
        const target = event.target.closest('.remove-contributor');
        if (!target) return;
        const row = target.closest('.contributor-row');
        row?.remove();
    });
});
</script>
@endsection
