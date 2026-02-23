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
        return view('admin.users.index', compact('users'));
    }

    public function pending()
    {
        $users = User::where('status', 'pending')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function blocked()
    {
        $users = User::where('status', 'blocked')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
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
}
