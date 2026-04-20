<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminActivityService;
use Illuminate\Http\Request;

class AdminManagementController extends Controller
{
    private array $permissionGroups = [
        'users' => ['view', 'approve', 'block', 'unblock', 'update'],
        'events' => ['view', 'create', 'update', 'delete'],
        'articles' => ['view', 'create', 'update', 'approve', 'delete'],
        'courses' => ['view', 'create', 'update', 'delete'],
        'memberships' => ['view', 'create', 'update', 'delete', 'approve'],
        'forums' => ['view', 'create', 'update', 'delete', 'moderate', 'invite'],
        'transactions' => ['view'],
        'podcasts' => ['view', 'create', 'update', 'delete'],
        'mentors' => ['view', 'create', 'update'],
    ];

    public function index()
    {
        $admins = Admin::query()->latest()->paginate(15);
        return view('admin.admins.index', [
            'admins' => $admins,
            'permissions' => $this->flattenPermissions(),
            'permissionGroups' => $this->permissionGroups,
        ]);
    }

    public function create()
    {
        return view('admin.admins.create', [
            'permissions' => $this->flattenPermissions(),
            'permissionGroups' => $this->permissionGroups,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,editor',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
        ]);

        $permissions = $this->sanitizePermissions($request->input('permissions', []));
        $validated['permissions'] = $permissions;
        $validated['is_active'] = $request->boolean('is_active');

        if ($this->isSuperAdminEmail($validated['email'])) {
            $validated['role'] = 'super_admin';
            $validated['permissions'] = $this->flattenPermissions();
        }

        $createdAdmin = Admin::create($validated);
        AdminActivityService::log(auth('admin')->user(), 'admin_create', $createdAdmin, [
            'created_email' => $validated['email'],
        ], 'Created a new admin');

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    public function edit(Admin $admin)
    {
        return view('admin.admins.edit', [
            'admin' => $admin,
            'permissions' => $this->flattenPermissions(),
            'permissionGroups' => $this->permissionGroups,
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,editor',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
        ]);

        $permissions = $this->sanitizePermissions($request->input('permissions', []));
        $validated['permissions'] = $permissions;
        $validated['is_active'] = $request->boolean('is_active');

        if ($this->isSuperAdminEmail($validated['email']) || $admin->isSuperAdmin()) {
            $validated['role'] = 'super_admin';
            $validated['permissions'] = $this->flattenPermissions();
            $validated['is_active'] = true;
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $admin->update($validated);
        AdminActivityService::log(auth('admin')->user(), 'admin_update', $admin, [], 'Updated admin account');

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        if ($admin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Super admin cannot be deleted.');
        }

        $admin->delete();
        AdminActivityService::log(auth('admin')->user(), 'admin_delete', $admin, [], 'Deleted admin account');

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    private function sanitizePermissions(array $permissions): array
    {
        return array_values(array_intersect($this->flattenPermissions(), $permissions));
    }

    private function isSuperAdminEmail(string $email): bool
    {
        $email = strtolower($email);
        return in_array($email, ['enquiries@wgrcfp.org'], true);
    }

    private function flattenPermissions(): array
    {
        $flat = [];
        foreach ($this->permissionGroups as $module => $actions) {
            foreach ($actions as $action) {
                $flat[] = "{$module}.{$action}";
            }
        }

        return $flat;
    }
}
