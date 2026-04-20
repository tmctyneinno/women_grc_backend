@extends('admin.layouts.app')

@section('title', 'Create Membership Tier')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('memberships.update'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Add Tier for {{ $membership->name }}</h3>
            <a href="{{ route('admin.membership-plans.tiers.index', $membership) }}" class="btn btn-alt-secondary">Back</a>
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

            <form method="POST" action="{{ route('admin.membership-plans.tiers.store', $membership) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tier Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Annual Fee (GBP)</label>
                    <input type="number" step="0.01" min="0" name="annual_fee" class="form-control" value="{{ old('annual_fee') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Target Audience</label>
                    <input type="text" name="target_audience" class="form-control" value="{{ old('target_audience') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Benefits</label>
                    <textarea name="benefits" class="form-control" rows="5" placeholder="One benefit per line or comma-separated">{{ old('benefits') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Invitation Only (optional)</label>
                    <input type="text" name="invitation_only" class="form-control" value="{{ old('invitation_only') }}">
                </div>
                <div class="d-flex gap-2">
                    @if($canUpdate)
                        <button class="btn btn-primary" type="submit">Create Tier</button>
                    @endif
                    <a href="{{ route('admin.membership-plans.tiers.index', $membership) }}" class="btn btn-alt-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
