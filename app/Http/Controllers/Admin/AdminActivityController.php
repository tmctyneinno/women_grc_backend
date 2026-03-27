<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        $adminId = $request->query('admin_id');
        $action = $request->query('action');
        $search = trim((string) $request->query('q', ''));

        $logs = AdminActivityLog::with('admin')
            ->when($adminId, fn ($q) => $q->where('admin_id', $adminId))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('target_type', 'like', "%{$search}%")
                        ->orWhere('target_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $admins = Admin::orderBy('name')->get(['id', 'name', 'email']);
        $actions = AdminActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.admins.activity', compact('logs', 'admins', 'actions', 'adminId', 'action', 'search'));
    }
}
