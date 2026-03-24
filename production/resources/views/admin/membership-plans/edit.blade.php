@extends('admin.layouts.app')

@section('title', 'Edit Membership Plan')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Edit Membership Plan</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.membership-plans.tiers.index', $membership) }}" class="btn btn-alt-primary">
                    Manage Tiers
                </a>
                <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-alt-secondary">Back</a>
            </div>
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

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.membership-plans.update', $membership) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Plan Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $membership->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $membership->description) }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Update Plan</button>
                    <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-alt-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
