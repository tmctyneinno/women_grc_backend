<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\AdminActivityService;

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
        AdminActivityService::log(auth('admin')->user(), 'user_approve', $user, [], 'Approved user');

        return back()->with('success', 'User verified successfully.');
    }

    public function block(User $user)
    {
        $user->update([
            'status' => 'blocked'
        ]);
        AdminActivityService::log(auth('admin')->user(), 'user_block', $user, [], 'Blocked user');

        return back()->with('success', 'User blocked successfully.');
    }

    public function unblock(User $user)
    {
        $user->update([
            'status' => 'verified'
        ]);
        AdminActivityService::log(auth('admin')->user(), 'user_unblock', $user, [], 'Unblocked user');

        return back()->with('success', 'User unblocked successfully.');
    }

    public function profile(User $user)
    {
        $admin = auth('admin')->user();
        if (!$admin || !$admin->hasPermission('users.view')) {
            abort(403);
        }

        $user->load([
            'memberships.membership:id,name',
            'memberships.tier:id,name',
        ]);

        $recentBookings = $user->eventBookings()
            ->with('event:id,title,start_date')
            ->latest()
            ->take(10)
            ->get();

        $recentEnrollments = $user->courseEnrollments()
            ->with('course:id,title')
            ->latest()
            ->take(10)
            ->get();

        $recentTransactions = $user->transactions()
            ->with('items')
            ->latest()
            ->take(10)
            ->get();

        $recentForumMemberships = $user->forumMemberships()
            ->with('forum:id,title')
            ->latest()
            ->take(10)
            ->get();

        $recentForumThreads = $user->forumThreads()
            ->with('forum:id,title')
            ->latest()
            ->take(10)
            ->get();

        $recentForumPosts = $user->forumPosts()
            ->with('thread:id,title')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.profile', compact(
            'user',
            'recentBookings',
            'recentEnrollments',
            'recentTransactions',
            'recentForumMemberships',
            'recentForumThreads',
            'recentForumPosts'
        ));
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
