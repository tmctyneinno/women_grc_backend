<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\MentorApplication;
use App\Models\MentorshipApplication;
use App\Models\UserMembership;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MentorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$this->hasActiveMembership($user->id)) {
            return ApiResponse::error('You need an active membership to view mentors.', [], 403);
        }

        $q = trim((string) $request->query('q', ''));
        $region = $request->query('region');
        $country = $request->query('country');
        $domain = $request->query('domain');
        $language = $request->query('language');
        $availability = $request->query('availability');
        $sort = $request->query('sort');

        $query = Mentor::query()
            ->with('user:id,first_name,last_name,job_title,company,profile_picture')
            ->where('is_active', true);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('domain', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('bio', 'like', "%{$q}%")
                    ->orWhere('expertise_summary', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                    });
            });
        }

        if ($region) {
            $query->where('region', $region);
        }

        if ($country) {
            $query->where('country', $country);
        }

        if ($domain) {
            $query->where('domain', $domain);
        }

        if ($availability) {
            $query->where('availability_status', $availability);
        }

        if ($language) {
            $query->whereJsonContains('languages', $language);
        }

        if ($sort === 'highest_rating') {
            $query->orderByDesc('rating_avg');
        } elseif ($sort === 'most_active') {
            $query->orderByDesc('mentorships_completed');
        } elseif ($sort === 'newest') {
            $query->latest();
        } else {
            $query->orderBy('availability_status')->orderByDesc('rating_avg');
        }

        $mentors = $query->paginate(12);

        return ApiResponse::success($mentors, 'Mentors fetched successfully.');
    }

    public function show(Mentor $mentor)
    {
        $user = request()->user();
        if (!$user || !$this->hasActiveMembership($user->id)) {
            return ApiResponse::error('You need an active membership to view mentors.', [], 403);
        }

        if (!$mentor->is_active) {
            return ApiResponse::error('Mentor not found.', [], 404);
        }

        $mentor->load('user:id,first_name,last_name,job_title,company,profile_picture');

        return ApiResponse::success($mentor, 'Mentor retrieved successfully.');
    }

    public function apply(Request $request, Mentor $mentor)
    {
        $user = $request->user();

        if (!$this->hasActiveMembership($user->id)) {
            return ApiResponse::error('You need an active membership to apply for mentorship.', [], 403);
        }

        if (!$mentor->is_active || $mentor->availability_status === 'not_taking') {
            return ApiResponse::error('This mentor is not accepting new mentees.', [], 403);
        }

        $existing = MentorshipApplication::where('mentor_id', $mentor->id)
            ->where('mentee_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return ApiResponse::error('You already have a pending or accepted application for this mentor.', [], 409);
        }

        $maxMentees = $mentor->max_mentees;
        if ($maxMentees === null) {
            $setting = MentorshipSetting::query()->first();
            $maxMentees = $setting?->max_mentees_per_mentor;
        }

        if ($maxMentees) {
            $activeCount = $mentor->mentorships()->where('status', 'active')->count();
            if ($activeCount >= $maxMentees) {
                return ApiResponse::error('Mentor is currently at capacity.', [], 400);
            }
        }

        $validated = $request->validate([
            'goals' => 'required|string|max:2000',
            'preferred_duration' => 'nullable|string|max:100',
            'availability' => 'nullable|string|max:2000',
            'communication_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $application = MentorshipApplication::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $user->id,
            'goals' => $validated['goals'],
            'preferred_duration' => $validated['preferred_duration'] ?? null,
            'availability' => $validated['availability'] ?? null,
            'communication_method' => $validated['communication_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        $mentorUser = $mentor->user;
        if ($mentorUser && $mentorUser->email) {
            try {
                Mail::raw('You have a new mentorship application on WGRCFP.', function ($mail) use ($mentorUser) {
                    $mail->to($mentorUser->email)->subject('New Mentorship Application');
                });
            } catch (\Exception $e) {
                // Fail silently for now
            }
        }

        return ApiResponse::success($application, 'Application submitted successfully.');
    }

    public function applyAsMentor(Request $request)
    {
        $user = $request->user();

        if (!$this->hasActiveMembership($user->id)) {
            return ApiResponse::error('You need an active membership to apply as a mentor.', [], 403);
        }

        if (!$this->hasMentorMembership($user->id)) {
            return ApiResponse::error('Only mentor membership holders can apply to become mentors.', [], 403);
        }

        if ($user->mentorProfile) {
            return ApiResponse::error('You are already a mentor.', [], 409);
        }

        $existing = MentorApplication::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return ApiResponse::error('You already have a mentor application pending.', [], 409);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'domain' => 'required|string|max:150',
            'region' => 'required|string|max:150',
            'country' => 'required|string|max:150',
            'bio' => 'required|string|max:2000',
            'expertise_summary' => 'required|string|max:2000',
            'availability_status' => 'required|in:available,busy,not_taking',
            'languages' => 'required|string|max:500',
            'skills' => 'required|string|max:1000',
            'certifications' => 'required|string|max:1000',
            'max_mentees' => 'nullable|integer|min:1',
        ]);

        $application = MentorApplication::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'domain' => $validated['domain'],
            'region' => $validated['region'],
            'country' => $validated['country'],
            'bio' => $validated['bio'],
            'expertise_summary' => $validated['expertise_summary'],
            'availability_status' => $validated['availability_status'],
            'languages' => $this->csvToArray($validated['languages']),
            'skills' => $this->csvToArray($validated['skills']),
            'certifications' => $this->csvToArray($validated['certifications']),
            'max_mentees' => $validated['max_mentees'] ?? null,
            'status' => 'pending',
        ]);

        return ApiResponse::success($application, 'Mentor application submitted successfully.');
    }

    private function hasActiveMembership(int $userId): bool
    {
        $latestMembership = UserMembership::query()
            ->where('user_id', $userId)
            ->latest()
            ->first();

        if (!$latestMembership) {
            return false;
        }

        $isExpired = $latestMembership->expires_at
            ? Carbon::now()->greaterThan($latestMembership->expires_at)
            : false;
        $isApproved = ($latestMembership->approval_status ?? 'approved') === 'approved';

        return $latestMembership->status === 'active' && $isApproved && !$isExpired;
    }

    private function hasMentorMembership(int $userId): bool
    {
        $latestMembership = UserMembership::query()
            ->where('user_id', $userId)
            ->latest()
            ->first();

        if (!$latestMembership) {
            return false;
        }

        return (int) $latestMembership->membership_id === 3;
    }

    private function csvToArray(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $items = array_map('trim', explode(',', $value));
        return array_values(array_filter($items, fn ($item) => $item !== ''));
    }
}
