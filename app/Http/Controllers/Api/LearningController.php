<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CoursePurchase;
use App\Models\LearningPoint;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\QuizAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LearningController extends Controller
{
    public function courses()
    {
        $courses = Course::query()
            ->withCount(['modules', 'enrollments'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return ApiResponse::success($courses, 'Courses fetched successfully.');
    }

    public function show(Course $course)
    {
        if ($course->status !== 'published' || !$course->is_active) {
            return ApiResponse::error('Course is not currently available.', [], 404);
        }

        $user = Auth::guard('sanctum')->user();
        $isEnrolled = false;
        if ($user) {
            $isEnrolled = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();
        }

        $course->load([
            'modules' => function ($query) {
                $query->where('is_active', true)->withCount('quizzes')->orderBy('position');
            },
            'modules.lessons',
            'modules.quizzes',
        ]);

        $course->modules->each(function ($module) {
            $module->quizzes->makeHidden(['correct_answer']);
            $module->lessons->transform(function ($lesson) {
                $lesson->file_url = $this->resolveFileUrl($lesson->file_path);
                return $lesson;
            });
        });

        if (!$isEnrolled) {
            $course->modules->each(function ($module) {
                $module->setRelation('lessons', collect());
                $module->setRelation('quizzes', collect());
            });
        }

        return ApiResponse::success($course, 'Course details fetched successfully.');
    }

    public function enroll(Course $course)
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        if ($course->status !== 'published' || !$course->is_active) {
            return ApiResponse::error('Course is not currently available for enrollment.', [], 422);
        }

        if ($course->enrollment_type !== 'open') {
            return ApiResponse::error(
                'This course is restricted. Please contact admin for access.',
                [],
                403
            );
        }

        if ($course->is_paid && (float) $course->price > 0) {
            $hasPaid = CoursePurchase::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'paid')
                ->exists();

            if (!$hasPaid) {
                return ApiResponse::error(
                    'Payment required before enrollment.',
                    [
                        'payment_required' => true,
                        'price' => $course->price,
                        'currency' => $course->currency,
                    ],
                    402
                );
            }
        }

        $enrollment = CourseEnrollment::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'status' => 'enrolled',
                'completion_percentage' => 0,
                'time_spent_minutes' => 0,
                'enrolled_at' => now(),
            ]
        );

        if ($enrollment->wasRecentlyCreated) {
            try {
                Mail::raw(
                    "Welcome to {$course->title}. Your learning progress has been initialized.",
                    function ($message) use ($user, $course) {
                        $message->to($user->email)->subject("Enrollment Confirmed: {$course->title}");
                    }
                );
            } catch (\Throwable $e) {
                // Email failures should not block enrollment.
            }
        }

        return ApiResponse::success($enrollment->load('course'), 'Enrollment successful.');
    }

    public function initiatePurchase(Course $course)
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        if (!$course->is_paid || (float) $course->price <= 0) {
            return ApiResponse::error('This course is free and does not require purchase.', [], 422);
        }

        $existingPaid = CoursePurchase::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'paid')
            ->first();

        if ($existingPaid) {
            return ApiResponse::success($existingPaid, 'Course already purchased.');
        }

        $pending = CoursePurchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'currency' => $course->currency ?: 'GBP',
            'status' => 'pending',
            'payment_reference' => 'CRS-' . strtoupper(Str::random(12)),
            'metadata' => [
                'initiated_at' => now()->toDateTimeString(),
            ],
        ]);

        return ApiResponse::success($pending, 'Purchase initiated. Complete payment to continue.');
    }

    public function confirmPurchase(Request $request, Course $course)
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        $data = $request->validate([
            'payment_reference' => 'required|string',
        ]);

        $purchase = CoursePurchase::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('payment_reference', $data['payment_reference'])
            ->first();

        if (!$purchase) {
            return ApiResponse::error('Purchase record not found.', [], 404);
        }

        $purchase->status = 'paid';
        $purchase->paid_at = now();
        $purchase->save();

        return ApiResponse::success($purchase, 'Payment confirmed successfully.');
    }

    public function myCourses()
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        $enrollments = CourseEnrollment::query()
            ->with(['course', 'lastModule'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        return ApiResponse::success($enrollments, 'Enrolled courses fetched successfully.');
    }

    public function updateModuleProgress(Request $request, Course $course, Module $module)
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        if ((int) $module->course_id !== (int) $course->id) {
            return ApiResponse::error('Module does not belong to this course.', [], 422);
        }

        $data = $request->validate([
            'is_completed' => 'required|boolean',
            'time_spent_minutes' => 'nullable|integer|min:0|max:720',
        ]);

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return ApiResponse::error('Enroll in the course first.', [], 403);
        }

        if ($course->navigation_mode === 'locked') {
            $previousModules = $course->modules()
                ->where('position', '<', $module->position)
                ->where('is_active', true)
                ->pluck('id');

            if ($previousModules->isNotEmpty()) {
                $completedCount = ModuleProgress::where('course_enrollment_id', $enrollment->id)
                    ->whereIn('module_id', $previousModules)
                    ->where('is_completed', true)
                    ->count();

                if ($completedCount !== $previousModules->count()) {
                    return ApiResponse::error('Complete prior modules first due to locked progression.', [], 422);
                }
            }
        }

        if ($data['is_completed'] && $module->quizzes()->count() > 0) {
            $hasPassedQuiz = QuizAttempt::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('module_id', $module->id)
                ->where('passed', true)
                ->exists();

            if (!$hasPassedQuiz) {
                return ApiResponse::error(
                    'You must pass the module quiz before marking this module complete.',
                    [],
                    422
                );
            }
        }

        $progress = ModuleProgress::firstOrCreate(
            [
                'course_enrollment_id' => $enrollment->id,
                'module_id' => $module->id,
            ]
        );
        $progress->time_spent_minutes += (int) ($data['time_spent_minutes'] ?? 0);
        $progress->is_completed = $data['is_completed'];
        $progress->completed_at = $data['is_completed'] ? now() : null;
        $progress->save();

        $enrollment->last_module_id = $module->id;
        $enrollment->time_spent_minutes += (int) ($data['time_spent_minutes'] ?? 0);
        $this->recalculateEnrollmentProgress($enrollment);
        $enrollment->save();
        $this->finalizeCourseCompletion($enrollment);

        if ($data['is_completed']) {
            $this->awardPoints($user->id, $course->id, 10, 'module_completed');
        }

        return ApiResponse::success($progress, 'Module progress updated successfully.');
    }

    public function submitModuleQuiz(Request $request, Course $course, Module $module)
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        if ((int) $module->course_id !== (int) $course->id) {
            return ApiResponse::error('Module does not belong to this course.', [], 422);
        }

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return ApiResponse::error('Enroll in the course first.', [], 403);
        }

        $questions = $module->quizzes()->get();
        if ($questions->isEmpty()) {
            return ApiResponse::error('No quiz is configured for this module.', [], 422);
        }

        $data = $request->validate([
            'answers' => 'required|array',
        ]);

        $attemptCount = QuizAttempt::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('module_id', $module->id)
            ->count();

        $maxAttempts = (int) ($questions->max('max_attempts') ?? 3);
        if ($attemptCount >= $maxAttempts) {
            return ApiResponse::error('You have reached the maximum number of attempts for this module.', [], 422);
        }

        $correct = 0;
        $total = $questions->count();

        foreach ($questions as $question) {
            $submitted = $data['answers'][$question->id] ?? null;
            if ($submitted !== null && (string) $submitted === (string) $question->correct_answer) {
                $correct++;
            }
        }

        $score = (int) round(($correct / max(1, $total)) * 100);
        $passMark = (int) ($questions->max('passing_threshold') ?: $course->passing_threshold);
        $passed = $score >= $passMark;

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'score' => $score,
            'passed' => $passed,
            'attempt_number' => $attemptCount + 1,
            'answers' => $data['answers'],
        ]);

        if ($passed) {
            ModuleProgress::updateOrCreate(
                [
                    'course_enrollment_id' => $enrollment->id,
                    'module_id' => $module->id,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now(),
                ]
            );

            $this->awardPoints($user->id, $course->id, 10, 'quiz_passed');
            $this->recalculateEnrollmentProgress($enrollment);
            $enrollment->save();
            $this->finalizeCourseCompletion($enrollment);
        }

        $response = [
            'attempt' => $attempt,
            'score' => $score,
            'passed' => $passed,
            'attempts_remaining' => max(0, $maxAttempts - ($attemptCount + 1)),
            'show_feedback' => (bool) $questions->every(fn ($q) => $q->show_feedback),
        ];

        return ApiResponse::success($response, 'Quiz submitted successfully.');
    }

    public function leaderboard(Request $request)
    {
        $region = $request->query('region');

        $query = LearningPoint::query()
            ->join('users', 'users.id', '=', 'learning_points.user_id')
            ->selectRaw('learning_points.user_id, users.first_name, users.last_name, SUM(learning_points.points) as total_points')
            ->groupBy('learning_points.user_id', 'users.first_name', 'users.last_name')
            ->orderByDesc('total_points');

        if ($region) {
            $query->where('users.company', 'like', "%{$region}%");
        }

        $leaders = $query->limit(20)->get();

        return ApiResponse::success($leaders, 'Leaderboard fetched successfully.');
    }

    public function achievements()
    {
        $user = Auth::guard('sanctum')->user();
        $verificationError = $this->ensureVerifiedMember($user);
        if ($verificationError) {
            return $verificationError;
        }

        $certificates = Certificate::with('course:id,title')
            ->where('user_id', $user->id)
            ->latest('issued_at')
            ->get();

        return ApiResponse::success($certificates, 'Achievements fetched successfully.');
    }

    public function verifyCertificate(string $verificationCode)
    {
        $certificate = Certificate::with(['user:id,first_name,last_name', 'course:id,title'])
            ->where('verification_code', $verificationCode)
            ->first();

        if (!$certificate) {
            return ApiResponse::error('Certificate not found.', [], 404);
        }

        return ApiResponse::success($certificate, 'Certificate verified.');
    }

    public function downloadCertificate(Certificate $certificate)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || (int) $certificate->user_id !== (int) $user->id) {
            return ApiResponse::error('Unauthorized certificate access.', [], 403);
        }

        $certificate->load(['user:id,first_name,last_name', 'course:id,title']);
        $verificationUrl = url("/api/v1/learning/certificates/verify/{$certificate->verification_code}");
        $logoPath = public_path('assets/media/photos/logo.png');
        $logoDataUri = $this->toDataUri($logoPath);
        $barcodeSvg = $this->buildVerificationBarcodeSvg(
            $certificate->verification_code ?: $certificate->certificate_code
        );

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('certificates.template', [
                'certificate' => $certificate,
                'learnerName' => trim(($certificate->user->first_name ?? '') . ' ' . ($certificate->user->last_name ?? '')),
                'courseTitle' => $certificate->course->title ?? '',
                'completionDate' => optional($certificate->issued_at)->format('F d, Y'),
                'verificationUrl' => $verificationUrl,
                'logoDataUri' => $logoDataUri,
                'barcodeSvg' => $barcodeSvg,
            ]);

            return $pdf->stream("certificate-{$certificate->certificate_code}.pdf");
        }

        $html = view('certificates.template', [
            'certificate' => $certificate,
            'learnerName' => trim(($certificate->user->first_name ?? '') . ' ' . ($certificate->user->last_name ?? '')),
            'courseTitle' => $certificate->course->title ?? '',
            'completionDate' => optional($certificate->issued_at)->format('F d, Y'),
            'verificationUrl' => $verificationUrl,
            'logoDataUri' => $logoDataUri,
            'barcodeSvg' => $barcodeSvg,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=certificate-{$certificate->certificate_code}.html",
        ]);
    }

    private function ensureVerifiedMember($user)
    {
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $status = strtolower((string) ($user->status ?? ''));
        $hasVerifiedStatus = in_array($status, ['verified', 'approved', 'active'], true);
        $hasVerifiedFlag = (bool) ($user->is_verified ?? false);
        $hasVerifiedEmail = !empty($user->email_verified_at);

        if (!$hasVerifiedStatus && !$hasVerifiedFlag && !$hasVerifiedEmail) {
            return ApiResponse::error('Only verified members can access the Learning Center.', [], 403);
        }

        return null;
    }

    private function recalculateEnrollmentProgress(CourseEnrollment $enrollment): void
    {
        $totalModules = $enrollment->course->modules()->where('is_active', true)->count();
        $completedModules = ModuleProgress::where('course_enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $percentage = $totalModules > 0 ? (int) round(($completedModules / $totalModules) * 100) : 0;

        $enrollment->completion_percentage = $percentage;

        if ($percentage >= 100) {
            $enrollment->status = 'completed';
            $enrollment->completed_at = now();
        }
    }

    private function awardPoints(int $userId, int $courseId, int $points, string $reason): void
    {
        LearningPoint::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'points' => $points,
            'reason' => $reason,
        ]);
    }

    private function finalizeCourseCompletion(CourseEnrollment $enrollment): void
    {
        if ($enrollment->status !== 'completed') {
            return;
        }

        $alreadyAwarded = LearningPoint::where('user_id', $enrollment->user_id)
            ->where('course_id', $enrollment->course_id)
            ->where('reason', 'course_completed')
            ->exists();

        if (!$alreadyAwarded) {
            $this->awardPoints($enrollment->user_id, $enrollment->course_id, 50, 'course_completed');
        }

        $this->issueCertificateIfEligible($enrollment);
    }

    private function issueCertificateIfEligible(CourseEnrollment $enrollment): void
    {
        $course = $enrollment->course;
        if (!$course->has_certificate || $enrollment->status !== 'completed') {
            return;
        }

        $verificationCode = Str::uuid()->toString();

        Certificate::firstOrCreate(
            [
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
            ],
            [
                'certificate_code' => strtoupper(Str::random(14)),
                'verification_code' => $verificationCode,
                'qr_code' => url("/api/v1/learning/certificates/verify/" . $verificationCode),
                'issued_at' => now(),
            ]
        );
    }

    private function resolveFileUrl(?string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return $filePath;
        }

        if (str_starts_with($filePath, '/storage/')) {
            return url($filePath);
        }

        return $filePath;
    }

    private function toDataUri(string $path): ?string
    {
        if (!File::exists($path)) {
            return null;
        }

        $mime = File::mimeType($path) ?: 'image/png';
        $raw = File::get($path);

        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    private function buildVerificationBarcodeSvg(string $value): string
    {
        $seed = preg_replace('/[^A-Za-z0-9]/', '', $value) ?: 'WGRCFP';
        $hash = md5($seed);

        $x = 10;
        $bars = '';
        foreach (str_split($hash) as $hex) {
            $n = hexdec($hex);
            $width = 1 + ($n % 3);
            $height = 42 + (($n % 4) * 4);
            $y = 55 - $height;
            $bars .= '<rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" fill="#1f2f61" />';
            $x += $width + 1;
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="210" height="70" viewBox="0 0 210 70">'
            . '<rect width="210" height="70" fill="#ffffff" />'
            . $bars
            . '<text x="105" y="66" font-size="8" text-anchor="middle" fill="#5f6787">' . e($seed) . '</text>'
            . '</svg>';
    }
}
