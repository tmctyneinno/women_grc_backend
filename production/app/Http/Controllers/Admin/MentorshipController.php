<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\Mentorship;
use App\Models\MentorshipApplication;
use Illuminate\Http\Request;

class MentorshipController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->buildStats();

        $applications = MentorshipApplication::with(['mentor.user', 'mentee'])
            ->latest()
            ->paginate(20);

        return view('admin.mentorships.index', [
            'stats' => $stats,
            'applications' => $applications,
        ]);
    }

    public function pending()
    {
        $stats = $this->buildStats();

        $applications = MentorshipApplication::with(['mentor.user', 'mentee'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.mentorships.pending', [
            'stats' => $stats,
            'applications' => $applications,
        ]);
    }

    public function active()
    {
        $stats = $this->buildStats();

        $mentorships = Mentorship::with(['mentor.user', 'mentee'])
            ->where('status', 'active')
            ->latest()
            ->paginate(20);

        return view('admin.mentorships.active', [
            'stats' => $stats,
            'mentorships' => $mentorships,
        ]);
    }

    public function completed()
    {
        $stats = $this->buildStats();

        $mentorships = Mentorship::with(['mentor.user', 'mentee'])
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return view('admin.mentorships.completed', [
            'stats' => $stats,
            'mentorships' => $mentorships,
        ]);
    }

    private function buildStats(): array
    {
        return [
            'pending' => MentorshipApplication::where('status', 'pending')->count(),
            'active' => Mentorship::where('status', 'active')->count(),
            'completed' => Mentorship::where('status', 'completed')->count(),
            'mentors' => Mentor::where('is_active', true)->count(),
        ];
    }
}
