<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\UserMembership;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class MembershipController extends Controller
{
    // Get all memberships with tiers
    public function index()
    {
        $memberships = Membership::with('tiers')->get();

        return ApiResponse::success($memberships);

        // return response()->json([
        //     'success' => true,
        //     'data' => $memberships
        // ]);
    }

    // Get a single membership by ID
    public function show($id)
    {
        $membership = Membership::with('tiers')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $membership
        ]);
    }

    // Optional: Create membership (Admin)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $membership = Membership::create($data);

        return response()->json([
            'success' => true,
            'data' => $membership,
            'message' => 'Membership created'
        ]);
    }

    // Optional: Update membership (Admin)
    public function update(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $membership->update($data);

        return response()->json([
            'success' => true,
            'data' => $membership,
            'message' => 'Membership updated'
        ]);
    }

    public function myStatus()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $latestMembership = UserMembership::query()
            ->with(['membership:id,name,description', 'tier:id,membership_id,name,annual_fee,target_audience'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$latestMembership) {
            return ApiResponse::success([
                'has_membership' => false,
                'is_active' => false,
                'is_expired' => false,
                'membership' => null,
            ]);
        }

        $isExpired = $latestMembership->expires_at
            ? Carbon::now()->greaterThan($latestMembership->expires_at)
            : false;
        $isApproved = ($latestMembership->approval_status ?? 'approved') === 'approved';
        $isActive = $latestMembership->status === 'active' && $isApproved && !$isExpired;

        return ApiResponse::success([
            'has_membership' => true,
            'is_active' => $isActive,
            'is_approved' => $isApproved,
            'is_expired' => $isExpired,
            'membership' => $latestMembership,
        ]);
    }
}
