@extends('admin.layouts.app')

@section('title', 'Edit Mentor')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Edit Mentor</h3>
            <a href="{{ route('admin.mentors.index') }}" class="btn btn-sm btn-alt-secondary">&larr; Back</a>
        </div>
        <div class="block-content">
            <form action="{{ route('admin.mentors.update', $mentor) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">User</label>
                    <input type="text" class="form-control" value="{{ $mentor->user ? trim(($mentor->user->first_name ?? '') . ' ' . ($mentor->user->last_name ?? '')) : 'N/A' }}" disabled>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $mentor->title) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Domain</label>
                        <input type="text" name="domain" class="form-control" value="{{ old('domain', $mentor->domain) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <input type="text" name="region" class="form-control" value="{{ old('region', $mentor->region) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $mentor->country) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3">{{ old('bio', $mentor->bio) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Expertise Summary</label>
                        <textarea name="expertise_summary" class="form-control" rows="3">{{ old('expertise_summary', $mentor->expertise_summary) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Availability Status</label>
                        <select name="availability_status" class="form-select" required>
                            <option value="available" {{ old('availability_status', $mentor->availability_status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="busy" {{ old('availability_status', $mentor->availability_status) === 'busy' ? 'selected' : '' }}>Busy</option>
                            <option value="not_taking" {{ old('availability_status', $mentor->availability_status) === 'not_taking' ? 'selected' : '' }}>Not Taking New Mentees</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Mentees</label>
                        <input type="number" name="max_mentees" class="form-control" min="1" value="{{ old('max_mentees', $mentor->max_mentees) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Languages (comma-separated)</label>
                        <input type="text" name="languages" class="form-control" value="{{ old('languages', is_array($mentor->languages) ? implode(', ', $mentor->languages) : '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Skills (comma-separated)</label>
                        <input type="text" name="skills" class="form-control" value="{{ old('skills', is_array($mentor->skills) ? implode(', ', $mentor->skills) : '') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Certifications (comma-separated)</label>
                        <input type="text" name="certifications" class="form-control" value="{{ old('certifications', is_array($mentor->certifications) ? implode(', ', $mentor->certifications) : '') }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
