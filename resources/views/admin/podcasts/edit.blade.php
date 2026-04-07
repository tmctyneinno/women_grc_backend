@extends('admin.layouts.app')

@section('title','Edit Podcast')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('podcasts.update'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Edit Podcast</h3>
            <a href="{{ route('admin.podcasts.index') }}" class="btn btn-alt-secondary">Back</a>
        </div>

        <div class="block-content">
            <form method="POST" action="{{ route('admin.podcasts.update', $podcast) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $podcast->title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tag</label>
                        <input type="text" name="tag" class="form-control" value="{{ old('tag', $podcast->tag) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" class="form-control" value="{{ old('duration', $podcast->duration) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" @selected(old('status', $podcast->status) === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $podcast->status) === 'published')>Published</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $podcast->is_active))>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Summary</label>
                        <textarea name="summary" class="form-control" rows="2">{{ old('summary', $podcast->summary) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $podcast->description) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Replace Audio File</label>
                        <input type="file" name="audio_file" class="form-control">
                        @if($podcast->audio_url)
                            <div class="form-text">
                                Current file: <a href="{{ $podcast->audio_url }}" target="_blank">Listen</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Replace Cover Image</label>
                        <input type="file" name="cover_image" class="form-control">
                        @if($podcast->cover_url)
                            <div class="mt-2">
                                <img src="{{ $podcast->cover_url }}" alt="cover" width="80" height="80" style="object-fit:cover;border-radius:8px;">
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Contributors</h5>
                    @if($canUpdate)
                        <button type="button" class="btn btn-sm btn-alt-primary" id="addContributor">
                            <i class="fa fa-plus"></i> Add Contributor
                        </button>
                    @endif
                </div>

                <div id="contributorsWrapper" class="d-grid gap-3">
                    @forelse($podcast->contributors as $contributor)
                        <div class="row g-2 contributor-row">
                            <input type="hidden" name="contributor_id[]" value="{{ $contributor->id }}">
                            <div class="col-md-4">
                                <input type="text" name="contributor_name[]" class="form-control" value="{{ $contributor->name }}" placeholder="Name">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="contributor_role[]" class="form-control" value="{{ $contributor->role }}" placeholder="Role / Title">
                            </div>
                            <div class="col-md-3">
                                <input type="file" name="contributor_photo[]" class="form-control">
                                @if($contributor->photo_url)
                                    <div class="mt-2">
                                        <img src="{{ $contributor->photo_url }}" alt="photo" width="42" height="42" style="object-fit:cover;border-radius:50%;">
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-alt-danger remove-contributor">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
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
                    @endforelse
                </div>

                <div class="mt-4">
                    @if($canUpdate)
                        <button class="btn btn-primary">Update Podcast</button>
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
