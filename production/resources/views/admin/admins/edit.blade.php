@extends('admin.layouts.app')

@section('title', 'Edit Admin')

@section('content')
<style>
    .admin-edit-page {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 45%, #f8fafc 100%);
        border-radius: 16px;
        padding: 22px;
    }
    .admin-edit-hero {
        background: radial-gradient(1200px 300px at 0% 0%, #fde68a 0%, rgba(253, 230, 138, 0) 60%),
                    linear-gradient(90deg, #1d4ed8 0%, #0ea5e9 45%, #22c55e 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 12px 28px rgba(2, 6, 23, 0.18);
    }
    .admin-edit-hero .badge {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
    }
    .admin-edit-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .admin-edit-card .block-header {
        background: linear-gradient(90deg, #f1f5f9 0%, #eef2ff 60%, #f0f9ff 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .admin-section-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #0f172a;
    }
    .admin-section-title i {
        color: #2563eb;
    }
    .admin-perm-group {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        background: #ffffff;
    }
    .admin-perm-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #1f2937;
    }
    .admin-perm-label .dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #22c55e;
        display: inline-block;
    }
    .admin-edit-footer {
        background: #f8fafc;
        border-top: 1px dashed #e2e8f0;
        border-radius: 0 0 14px 14px;
        padding: 14px 16px;
    }
    .dark .admin-edit-page {
        background: linear-gradient(180deg, #0b1220 0%, #0f172a 50%, #0b1220 100%);
    }
    .dark .admin-edit-card {
        border-color: #1f2937;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
    }
    .dark .admin-edit-card .block-header {
        background: linear-gradient(90deg, #111827 0%, #1f2937 60%, #0f172a 100%);
        border-bottom-color: #1f2937;
    }
    .dark .admin-perm-group {
        background: #0b1220;
        border-color: #1f2937;
    }
    .dark .admin-section-title,
    .dark .admin-perm-label {
        color: #e5e7eb;
    }
    .dark .admin-edit-footer {
        background: #0b1220;
        border-top-color: #1f2937;
    }
</style>
<div class="content">
    @php($adminUser = auth('admin')->user())
    @php($canManageAdmins = $adminUser && $adminUser->isSuperAdmin())
    <div class="admin-edit-page">
        <div class="admin-edit-hero mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="badge mb-2">Admin Management</div>
                    <h1 class="h3 fw-bold mb-1">Edit Admin Profile</h1>
                    <div class="opacity-75">Update admin details, access level, and permissions.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light">
                        <i class="fa fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="block admin-edit-card block-rounded">
            <div class="block-header">
                <h3 class="block-title admin-section-title">
                    <i class="fa fa-user-shield"></i> Admin Details
                </h3>
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

            <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password (optional)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'editor' => 'Editor'] as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $admin->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h4 class="fs-5 mb-3 admin-section-title">
                    <i class="fa fa-key"></i> Permissions
                </h4>
                @php($selectedPerms = old('permissions', $admin->permissions ?? []))
                @foreach(($permissionGroups ?? []) as $module => $actions)
                    <div class="admin-perm-group mb-3">
                        <div class="admin-perm-label text-uppercase mb-3">
                            <span class="dot"></span> {{ ucfirst($module) }}
                        </div>
                        <div class="row g-2">
                            @foreach($actions as $action)
                                @php($permKey = $module . '.' . $action)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="perm_{{ $permKey }}" name="permissions[]" value="{{ $permKey }}" {{ in_array($permKey, $selectedPerms, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $permKey }}">{{ ucfirst($action) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="admin-edit-footer d-flex flex-wrap gap-2 justify-content-between align-items-center mt-4">
                    <div class="text-muted fs-sm">
                        Tip: Assign only the minimum permissions needed for each admin.
                    </div>
                    <div class="d-flex gap-2">
                        @if($canManageAdmins)
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save me-1"></i> Save Changes
                            </button>
                        @endif
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-alt-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection
