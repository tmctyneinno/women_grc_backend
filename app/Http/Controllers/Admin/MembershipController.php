<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index()
    {
        $memberships = Membership::withCount('tiers')->latest()->paginate(10);
        return view('admin.membership-plans.index', compact('memberships'));
    }

    public function create()
    {
        return view('admin.membership-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Membership::create($validated);

        return redirect()->route('admin.membership-plans.index')
            ->with('success', 'Membership plan created successfully.');
    }

    public function edit(Membership $membership)
    {
        $membership->load('tiers');
        return view('admin.membership-plans.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $membership->update($validated);

        return redirect()->route('admin.membership-plans.index')
            ->with('success', 'Membership plan updated successfully.');
    }

    public function destroy(Membership $membership)
    {
        $membership->delete();
        return back()->with('success', 'Membership plan deleted.');
    }
}
