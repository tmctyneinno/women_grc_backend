@extends('admin.layouts.app')

@section('title', 'Create Admin')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Create Admin</h3>
            <a href="{{ route('admin.admins.index') }}" class="btn btn-alt-secondary">Back</a>
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

            <form method="POST" action="{{ route('admin.admins.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'editor' => 'Editor'] as $value => $label)
                                <option value="{{ $value }}" {{ old('role', 'admin') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <hr>

                <h4 class="fs-5 mb-3">Permissions</h4>
                <div class="row g-2">
                    @foreach($permissions as $perm)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_{{ $perm }}" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, old('permissions', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $perm }}">{{ ucfirst($perm) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Create Admin</button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-alt-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
