<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);
        $stats = $this->buildStats();
        return view('admin.users.index', compact('users', 'stats'));
    }

    public function pending()
    {
        $users = User::where('status', 'pending')->latest()->paginate(20);
        $stats = $this->buildStats();
        return view('admin.users.index', compact('users', 'stats'));
    }

    public function blocked()
    {
        $users = User::where('status', 'blocked')->latest()->paginate(20);
        $stats = $this->buildStats();
        return view('admin.users.index', compact('users', 'stats'));
    }

    public function approve(User $user)
    {
        $user->update([
            'status' => 'verified',
        ]);

        return back()->with('success', 'User verified successfully.');
    }

    public function block(User $user)
    {
        $user->update([
            'status' => 'blocked'
        ]);

        return back()->with('success', 'User blocked successfully.');
    }

    private function buildStats(): array
    {
        return [
            'total' => User::count(),
            'verified' => User::where('status', 'verified')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
        ];
    }
}
