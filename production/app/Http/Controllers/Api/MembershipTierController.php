<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipTier;
use Illuminate\Http\Request;

class MembershipTierController extends Controller
{
    // Get all tiers
    public function index()
    {
        $tiers = MembershipTier::with('membership')->get();

        return response()->json([
            'success' => true,
            'data' => $tiers
        ]);
    }

    // Get a single tier
    public function show($id)
    {
        $tier = MembershipTier::with('membership')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tier
        ]);
    }

    // Create a tier (Admin)
    public function store(Request $request)
    {
        $data = $request->validate([
            'membership_id' => 'required|exists:memberships,id',
            'name' => 'required|string|max:255',
            'annual_fee' => 'required|numeric|min:0',
            'target_audience' => 'required|string',
            'benefits' => 'required|array',
            'invitation_only' => 'nullable|string'
        ]);

        $tier = MembershipTier::create($data);

        return response()->json([
            'success' => true,
            'data' => $tier,
            'message' => 'Membership tier created'
        ]);
    }

    // Update a tier (Admin)
    public function update(Request $request, $id)
    {
        $tier = MembershipTier::findOrFail($id);

        $data = $request->validate([
            'membership_id' => 'required|exists:memberships,id',
            'name' => 'required|string|max:255',
            'annual_fee' => 'required|numeric|min:0',
            'target_audience' => 'required|string',
            'benefits' => 'required|array',
            'invitation_only' => 'nullable|string'
        ]);

        $tier->update($data);

        return response()->json([
            'success' => true,
            'data' => $tier,
            'message' => 'Membership tier updated'
        ]);
    }
}