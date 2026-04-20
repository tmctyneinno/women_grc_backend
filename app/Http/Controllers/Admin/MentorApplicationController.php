<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\MentorApplication;
use Illuminate\Http\Request;

class MentorApplicationController extends Controller
{
    public function index()
    {
        $applications = MentorApplication::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.mentors.applications', compact('applications'));
    }

    public function approve(Request $request, MentorApplication $application)
    {
        if ($application->status !== 'pending') {
            return redirect()->route('admin.mentors.applications')
                ->with('error', 'Only pending applications can be approved.');
        }

        if (Mentor::where('user_id', $application->user_id)->exists()) {
            return redirect()->route('admin.mentors.applications')
                ->with('error', 'This user is already a mentor.');
        }

        Mentor::create([
            'user_id' => $application->user_id,
            'title' => $application->title,
            'domain' => $application->domain,
            'region' => $application->region,
            'country' => $application->country,
            'bio' => $application->bio,
            'expertise_summary' => $application->expertise_summary,
            'availability_status' => $application->availability_status,
            'languages' => $application->languages,
            'skills' => $application->skills,
            'certifications' => $application->certifications,
            'max_mentees' => $application->max_mentees,
            'is_active' => true,
            'created_by' => auth('admin')->id(),
        ]);

        $application->status = 'approved';
        $application->decided_at = now();
        $application->admin_notes = $request->input('admin_notes');
        $application->save();

        return redirect()->route('admin.mentors.applications')
            ->with('success', 'Mentor application approved and mentor created.');
    }

    public function decline(Request $request, MentorApplication $application)
    {
        if ($application->status !== 'pending') {
            return redirect()->route('admin.mentors.applications')
                ->with('error', 'Only pending applications can be declined.');
        }

        $application->status = 'declined';
        $application->decided_at = now();
        $application->admin_notes = $request->input('admin_notes');
        $application->save();

        return redirect()->route('admin.mentors.applications')
            ->with('success', 'Mentor application declined.');
    }
}
