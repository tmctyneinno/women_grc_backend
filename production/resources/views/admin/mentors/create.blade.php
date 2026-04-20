@extends('admin.layouts.app')

@section('title', 'Add Mentor')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Add Mentor</h3>
            <a href="{{ route('admin.mentors.index') }}" class="btn btn-sm btn-alt-secondary">&larr; Back</a>
        </div>
        <div class="block-content">
            <form action="{{ route('admin.mentors.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select a user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Professional Title">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Domain</label>
                        <input type="text" name="domain" class="form-control" value="{{ old('domain') }}" placeholder="Specialization Domain">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <input type="text" name="region" class="form-control" value="{{ old('region') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3">{{ old('bio') }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Expertise Summary</label>
                        <textarea name="expertise_summary" class="form-control" rows="3">{{ old('expertise_summary') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Availability Status</label>
                        <select name="availability_status" class="form-select" required>
                            <option value="available" {{ old('availability_status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="busy" {{ old('availability_status') === 'busy' ? 'selected' : '' }}>Busy</option>
                            <option value="not_taking" {{ old('availability_status') === 'not_taking' ? 'selected' : '' }}>Not Taking New Mentees</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Mentees</label>
                        <input type="number" name="max_mentees" class="form-control" min="1" value="{{ old('max_mentees') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Languages (comma-separated)</label>
                        <input type="text" name="languages" class="form-control" value="{{ old('languages') }}" placeholder="English, French">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Skills (comma-separated)</label>
                        <input type="text" name="skills" class="form-control" value="{{ old('skills') }}" placeholder="AML, ESG, Data Privacy">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Certifications (comma-separated)</label>
                        <input type="text" name="certifications" class="form-control" value="{{ old('certifications') }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Mentor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
