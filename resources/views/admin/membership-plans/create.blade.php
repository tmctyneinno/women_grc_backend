@extends('admin.layouts.app')

@section('title', 'Create Membership Plan')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canCreate = $admin && $admin->hasPermission('memberships.create'))
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Create Membership Plan</h3>
            <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-alt-secondary">Back</a>
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

            <form method="POST" action="{{ route('admin.membership-plans.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Plan Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    @if($canCreate)
                        <button class="btn btn-primary" type="submit">Create Plan</button>
                    @endif
                    <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-alt-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
