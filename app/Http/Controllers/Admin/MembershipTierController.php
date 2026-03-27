<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipTier;
use App\Services\AdminActivityService;
use Illuminate\Http\Request;

class MembershipTierController extends Controller
{
    public function index(Membership $membership)
    {
        $tiers = $membership->tiers()->latest()->paginate(10);
        return view('admin.membership-plans.tiers.index', compact('membership', 'tiers'));
    }

    public function create(Membership $membership)
    {
        return view('admin.membership-plans.tiers.create', compact('membership'));
    }

    public function store(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'annual_fee' => 'required|numeric|min:0',
            'target_audience' => 'required|string|max:255',
            'benefits' => 'nullable|string',
            'invitation_only' => 'nullable|string|max:255',
        ]);

        $validated['benefits'] = $this->parseBenefits($validated['benefits'] ?? '');
        $validated['created_by'] = auth()->guard('admin')->id();
        $tier = $membership->tiers()->create($validated);
        AdminActivityService::log(auth('admin')->user(), 'membership_tier_create', $tier, [
            'membership_id' => $membership->id,
        ], 'Created membership tier');

        return redirect()->route('admin.membership-plans.tiers.index', $membership)
            ->with('success', 'Membership tier created successfully.');
    }

    public function edit(Membership $membership, MembershipTier $tier)
    {
        $this->ensureTierBelongsToMembership($membership, $tier);
        return view('admin.membership-plans.tiers.edit', compact('membership', 'tier'));
    }

    public function update(Request $request, Membership $membership, MembershipTier $tier)
    {
        $this->ensureTierBelongsToMembership($membership, $tier);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'annual_fee' => 'required|numeric|min:0',
            'target_audience' => 'required|string|max:255',
            'benefits' => 'nullable|string',
            'invitation_only' => 'nullable|string|max:255',
        ]);

        $validated['benefits'] = $this->parseBenefits($validated['benefits'] ?? '');
        $tier->update($validated);
        AdminActivityService::log(auth('admin')->user(), 'membership_tier_update', $tier, [], 'Updated membership tier');

        return redirect()->route('admin.membership-plans.tiers.index', $membership)
            ->with('success', 'Membership tier updated successfully.');
    }

    public function destroy(Membership $membership, MembershipTier $tier)
    {
        $this->ensureTierBelongsToMembership($membership, $tier);
        $tier->delete();
        AdminActivityService::log(auth('admin')->user(), 'membership_tier_delete', $tier, [], 'Deleted membership tier');

        return back()->with('success', 'Membership tier deleted.');
    }

    private function parseBenefits(string $benefits): array
    {
        if (!$benefits) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $benefits);
        if (count($lines) <= 1) {
            $lines = explode(',', $benefits);
        }

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function ensureTierBelongsToMembership(Membership $membership, MembershipTier $tier): void
    {
        if ((int) $tier->membership_id !== (int) $membership->id) {
            abort(404);
        }
    }
}
