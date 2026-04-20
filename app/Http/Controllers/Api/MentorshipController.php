<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\Mentorship;
use App\Models\MentorshipApplication;
use App\Models\MentorshipFeedback;
use App\Models\MentorshipMilestone;
use App\Models\MentorshipNote;
use App\Models\MentorshipSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MentorshipController extends Controller
{
    public function applications(Request $request)
    {
        $user = $request->user();
        $role = $request->query('role', 'mentee');

        if ($role === 'mentor') {
            $mentor = $user->mentorProfile;
            if (!$mentor || !$mentor->is_active) {
                return ApiResponse::error('You are not a mentor.', [], 403);
            }

            $applications = MentorshipApplication::with(['mentee'])
                ->where('mentor_id', $mentor->id)
                ->latest()
                ->paginate(20);

            return ApiResponse::success($applications, 'Mentor applications fetched.');
        }

        $applications = MentorshipApplication::with(['mentor.user'])
            ->where('mentee_id', $user->id)
            ->latest()
            ->paginate(20);

        return ApiResponse::success($applications, 'Mentee applications fetched.');
    }

    public function accept(Request $request, MentorshipApplication $application)
    {
        $user = $request->user();
        $mentor = $user->mentorProfile;

        if (!$mentor || (int) $application->mentor_id !== (int) $mentor->id) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        if ($application->status !== 'pending') {
            return ApiResponse::error('Application is not pending.', [], 400);
        }

        $durationMonths = $this->parseDurationMonths($application->preferred_duration);
        $startDate = Carbon::today();
        $endDate = $durationMonths ? $startDate->copy()->addMonths($durationMonths) : null;

        $mentorship = Mentorship::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $application->mentee_id,
            'application_id' => $application->id,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => $durationMonths,
            'goal_summary' => $application->goals,
            'communication_method' => $application->communication_method,
            'progress_percentage' => 0,
        ]);

        $application->status = 'accepted';
        $application->decided_at = now();
        $application->save();

        $mentee = $application->mentee;
        if ($mentee && $mentee->email) {
            try {
                Mail::raw('Your mentorship application has been accepted.', function ($mail) use ($mentee) {
                    $mail->to($mentee->email)->subject('Mentorship Application Accepted');
                });
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        return ApiResponse::success($mentorship, 'Application accepted.');
    }

    public function decline(Request $request, MentorshipApplication $application)
    {
        $user = $request->user();
        $mentor = $user->mentorProfile;

        if (!$mentor || (int) $application->mentor_id !== (int) $mentor->id) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        if ($application->status !== 'pending') {
            return ApiResponse::error('Application is not pending.', [], 400);
        }

        $validated = $request->validate([
            'mentor_feedback' => 'nullable|string|max:2000',
        ]);

        $application->status = 'declined';
        $application->mentor_feedback = $validated['mentor_feedback'] ?? null;
        $application->decided_at = now();
        $application->save();

        $mentee = $application->mentee;
        if ($mentee && $mentee->email) {
            try {
                Mail::raw('Your mentorship application was declined.', function ($mail) use ($mentee) {
                    $mail->to($mentee->email)->subject('Mentorship Application Update');
                });
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        return ApiResponse::success($application, 'Application declined.');
    }

    public function myMentorships(Request $request)
    {
        $user = $request->user();
        $role = $request->query('role');

        $query = Mentorship::with(['mentor.user', 'mentee']);

        if ($role === 'mentor') {
            $mentor = $user->mentorProfile;
            if (!$mentor) {
                return ApiResponse::error('You are not a mentor.', [], 403);
            }
            $query->where('mentor_id', $mentor->id);
        } elseif ($role === 'mentee') {
            $query->where('mentee_id', $user->id);
        } else {
            $mentor = $user->mentorProfile;
            $query->where(function ($sub) use ($user, $mentor) {
                $sub->where('mentee_id', $user->id);
                if ($mentor) {
                    $sub->orWhere('mentor_id', $mentor->id);
                }
            });
        }

        $mentorships = $query->latest()->paginate(20);

        return ApiResponse::success($mentorships, 'Mentorships fetched.');
    }

    public function show(Request $request, Mentorship $mentorship)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship)) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $mentorship->load(['mentor.user', 'mentee', 'milestones', 'sessions', 'notes', 'feedbacks']);

        return ApiResponse::success($mentorship, 'Mentorship retrieved.');
    }

    public function addMilestone(Request $request, Mentorship $mentorship)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship)) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'order' => 'nullable|integer|min:0',
        ]);

        $milestone = MentorshipMilestone::create([
            'mentorship_id' => $mentorship->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'order' => $validated['order'] ?? null,
            'status' => 'pending',
        ]);

        return ApiResponse::success($milestone, 'Milestone created.');
    }

    public function updateMilestone(Request $request, Mentorship $mentorship, MentorshipMilestone $milestone)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship) || $milestone->mentorship_id !== $mentorship->id) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,completed',
        ]);

        if (($validated['status'] ?? null) === 'completed') {
            $validated['completed_at'] = now();
        }

        $milestone->update($validated);

        return ApiResponse::success($milestone, 'Milestone updated.');
    }

    public function addSession(Request $request, Mentorship $mentorship)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship)) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:15',
            'meeting_link' => 'nullable|string|max:500',
            'status' => 'nullable|in:scheduled,completed,cancelled',
            'notes_shared' => 'nullable|string|max:2000',
            'mentor_notes' => 'nullable|string|max:2000',
            'mentee_notes' => 'nullable|string|max:2000',
        ]);

        $session = MentorshipSession::create([
            'mentorship_id' => $mentorship->id,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'meeting_link' => $validated['meeting_link'] ?? null,
            'status' => $validated['status'] ?? 'scheduled',
            'notes_shared' => $validated['notes_shared'] ?? null,
            'mentor_notes' => $validated['mentor_notes'] ?? null,
            'mentee_notes' => $validated['mentee_notes'] ?? null,
        ]);

        return ApiResponse::success($session, 'Session created.');
    }

    public function updateSession(Request $request, Mentorship $mentorship, MentorshipSession $session)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship) || $session->mentorship_id !== $mentorship->id) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:15',
            'meeting_link' => 'nullable|string|max:500',
            'status' => 'nullable|in:scheduled,completed,cancelled',
            'notes_shared' => 'nullable|string|max:2000',
            'mentor_notes' => 'nullable|string|max:2000',
            'mentee_notes' => 'nullable|string|max:2000',
        ]);

        $session->update($validated);

        return ApiResponse::success($session, 'Session updated.');
    }

    public function addNote(Request $request, Mentorship $mentorship)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship)) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'session_id' => 'nullable|exists:mentorship_sessions,id',
            'visibility' => 'nullable|in:shared,mentor_private,mentee_private',
            'content' => 'required|string|max:4000',
        ]);

        if (!empty($validated['session_id'])) {
            $session = MentorshipSession::find($validated['session_id']);
            if (!$session || (int) $session->mentorship_id !== (int) $mentorship->id) {
                return ApiResponse::error('Invalid session selected.', [], 422);
            }
        }

        $note = MentorshipNote::create([
            'mentorship_id' => $mentorship->id,
            'session_id' => $validated['session_id'] ?? null,
            'author_id' => $request->user()->id,
            'visibility' => $validated['visibility'] ?? 'shared',
            'content' => $validated['content'],
        ]);

        return ApiResponse::success($note, 'Note added.');
    }

    public function addFeedback(Request $request, Mentorship $mentorship)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship)) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'communication_quality' => 'required|integer|min:1|max:5',
            'goal_achievement' => 'required|integer|min:1|max:5',
            'engagement_frequency' => 'required|integer|min:1|max:5',
            'satisfaction' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:2000',
        ]);

        $role = $this->getUserRole($request->user(), $mentorship);
        if (!$role) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $feedback = MentorshipFeedback::updateOrCreate([
            'mentorship_id' => $mentorship->id,
            'user_id' => $request->user()->id,
        ], [
            'role' => $role,
            'communication_quality' => $validated['communication_quality'],
            'goal_achievement' => $validated['goal_achievement'],
            'engagement_frequency' => $validated['engagement_frequency'],
            'satisfaction' => $validated['satisfaction'],
            'comments' => $validated['comments'] ?? null,
            'submitted_at' => now(),
        ]);

        return ApiResponse::success($feedback, 'Feedback submitted.');
    }

    public function complete(Request $request, Mentorship $mentorship)
    {
        if (!$this->canAccessMentorship($request->user(), $mentorship)) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $mentorship->status = 'completed';
        $mentorship->completed_at = now();
        $mentorship->save();

        return ApiResponse::success($mentorship, 'Mentorship completed.');
    }

    private function canAccessMentorship($user, Mentorship $mentorship): bool
    {
        if ((int) $mentorship->mentee_id === (int) $user->id) {
            return true;
        }

        $mentor = $user->mentorProfile;
        if ($mentor && (int) $mentorship->mentor_id === (int) $mentor->id) {
            return true;
        }

        return false;
    }

    private function getUserRole($user, Mentorship $mentorship): ?string
    {
        if ((int) $mentorship->mentee_id === (int) $user->id) {
            return 'mentee';
        }

        $mentor = $user->mentorProfile;
        if ($mentor && (int) $mentorship->mentor_id === (int) $mentor->id) {
            return 'mentor';
        }

        return null;
    }

    private function parseDurationMonths(?string $input): ?int
    {
        if (!$input) {
            return null;
        }

        if (preg_match('/(\d+)/', $input, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
