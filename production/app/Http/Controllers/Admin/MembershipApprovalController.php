<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserMembership;
use App\Services\AdminActivityService;
use Illuminate\Http\Request;

class MembershipApprovalController extends Controller
{
    public function index()
    {
        $pendingMemberships = UserMembership::query()
            ->with(['user:id,first_name,last_name,email,linkedin_profile', 'membership:id,name', 'tier:id,name'])
            ->where('approval_status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.memberships.index', compact('pendingMemberships'));
    }

    public function approve(UserMembership $userMembership, Request $request)
    {
        if ($userMembership->approval_status === 'approved') {
            return redirect()->back()->with('success', 'Membership already approved.');
        }

        $userMembership->approval_status = 'approved';
        $userMembership->approved_at = now();
        $userMembership->approved_by_admin_id = $request->user()->id ?? null;
        $userMembership->save();
        AdminActivityService::log(auth('admin')->user(), 'membership_approve', $userMembership, [
            'user_id' => $userMembership->user_id,
        ], 'Approved membership');

        return redirect()->back()->with('success', 'Membership approved successfully.');
    }

    public function approveFromEmail(UserMembership $userMembership)
    {
        if ($userMembership->approval_status !== 'approved') {
            $userMembership->approval_status = 'approved';
            $userMembership->approved_at = now();
            $userMembership->approved_by_admin_id = null;
            $userMembership->save();
        }

        return view('admin.memberships.email-approved', compact('userMembership'));
    }
}
