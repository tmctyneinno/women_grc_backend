<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    public function index()
    {
        $mentors = Mentor::with('user')->latest()->paginate(20);
        return view('admin.mentors.index', compact('mentors'));
    }

    public function create()
    {
        $users = User::whereDoesntHave('mentorProfile')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.mentors.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:mentors,user_id',
            'title' => 'nullable|string|max:150',
            'domain' => 'nullable|string|max:150',
            'region' => 'nullable|string|max:150',
            'country' => 'nullable|string|max:150',
            'bio' => 'nullable|string|max:2000',
            'expertise_summary' => 'nullable|string|max:2000',
            'availability_status' => 'required|in:available,busy,not_taking',
            'languages' => 'nullable|string',
            'skills' => 'nullable|string',
            'certifications' => 'nullable|string',
            'max_mentees' => 'nullable|integer|min:1',
        ]);

        $validated['languages'] = $this->csvToArray($validated['languages'] ?? null);
        $validated['skills'] = $this->csvToArray($validated['skills'] ?? null);
        $validated['certifications'] = $this->csvToArray($validated['certifications'] ?? null);

        $validated['created_by'] = auth('admin')->id();
        $validated['is_active'] = true;

        Mentor::create($validated);

        return redirect()->route('admin.mentors.index')->with('success', 'Mentor created successfully.');
    }

    public function edit(Mentor $mentor)
    {
        $mentor->load('user');
        return view('admin.mentors.edit', compact('mentor'));
    }

    public function update(Request $request, Mentor $mentor)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'domain' => 'nullable|string|max:150',
            'region' => 'nullable|string|max:150',
            'country' => 'nullable|string|max:150',
            'bio' => 'nullable|string|max:2000',
            'expertise_summary' => 'nullable|string|max:2000',
            'availability_status' => 'required|in:available,busy,not_taking',
            'languages' => 'nullable|string',
            'skills' => 'nullable|string',
            'certifications' => 'nullable|string',
            'max_mentees' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['languages'] = $this->csvToArray($validated['languages'] ?? null);
        $validated['skills'] = $this->csvToArray($validated['skills'] ?? null);
        $validated['certifications'] = $this->csvToArray($validated['certifications'] ?? null);

        $mentor->update($validated);

        return redirect()->route('admin.mentors.index')->with('success', 'Mentor updated successfully.');
    }

    public function toggle(Mentor $mentor)
    {
        $mentor->is_active = !$mentor->is_active;
        $mentor->save();

        return redirect()->route('admin.mentors.index')->with('success', 'Mentor status updated successfully.');
    }

    private function csvToArray(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $items = array_map('trim', explode(',', $value));
        return array_values(array_filter($items, fn ($item) => $item !== ''));
    }
}
