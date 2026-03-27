<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\LearningPoint;
use App\Models\QuizAttempt;
use App\Services\AdminActivityService;

class CourseController extends Controller
{
    public function index() {
        $courses = Course::withCount(['modules', 'enrollments', 'purchases'])->latest()->paginate(10);
        $stats = [
            'total' => Course::count(),
            'published' => Course::where('status', 'published')->count(),
            'draft' => Course::where('status', 'draft')->count(),
            'paid' => Course::where('is_paid', true)->count(),
        ];
        return view('admin.courses.index', compact('courses', 'stats'));
    }

    public function show(Course $course)
    {
        $course->loadCount(['modules', 'enrollments']);

        $enrollments = $course->enrollments()
            ->with('user:id,first_name,last_name,email,status')
            ->latest()
            ->paginate(10, ['*'], 'enrollments_page');

        $quizAttempts = QuizAttempt::query()
            ->where('course_id', $course->id)
            ->with([
                'user:id,first_name,last_name,email',
                'module:id,title',
            ])
            ->latest()
            ->paginate(10, ['*'], 'attempts_page');

        $learningPoints = LearningPoint::query()
            ->where('course_id', $course->id)
            ->with('user:id,first_name,last_name,email')
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->paginate(10, ['*'], 'points_page');

        $purchases = CoursePurchase::query()
            ->where('course_id', $course->id)
            ->where('status', 'paid')
            ->with('user:id,first_name,last_name,email')
            ->latest('paid_at')
            ->paginate(10, ['*'], 'purchases_page');

        return view(
            'admin.courses.show',
            compact('course', 'enrollments', 'quizAttempts', 'learningPoints', 'purchases')
        );
    }

    public function create() {
        return view('admin.courses.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'category' => 'nullable|string',
            'tags' => 'nullable|string',
            'has_certificate' => 'boolean',
            'status' => 'required|in:draft,published',
            'enrollment_type' => 'required|in:open,invite_only,premium',
            'navigation_mode' => 'required|in:free,locked',
            'passing_threshold' => 'required|integer|min:1|max:100',
            'requires_quiz_pass' => 'boolean',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $validated['tags'] = $request->tags
            ? array_values(array_filter(array_map('trim', explode(',', $request->tags))))
            : [];
        $validated['has_certificate'] = $request->boolean('has_certificate');
        $validated['requires_quiz_pass'] = $request->boolean('requires_quiz_pass');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['currency'] = strtoupper($request->input('currency', 'GBP'));
        $validated['price'] = $validated['is_paid'] ? (float) ($validated['price'] ?? 0) : 0;
        $validated['created_by'] = auth()->guard('admin')->id();

        $course = Course::create($validated);
        AdminActivityService::log(auth('admin')->user(), 'course_create', $course, [
            'title' => $course->title,
        ], 'Created course');

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully');
    }

    public function edit(Course $course) {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'category' => 'nullable|string',
            'tags' => 'nullable|string',
            'has_certificate' => 'boolean',
            'status' => 'required|in:draft,published',
            'enrollment_type' => 'required|in:open,invite_only,premium',
            'navigation_mode' => 'required|in:free,locked',
            'passing_threshold' => 'required|integer|min:1|max:100',
            'requires_quiz_pass' => 'boolean',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $validated['tags'] = $request->tags
            ? array_values(array_filter(array_map('trim', explode(',', $request->tags))))
            : [];
        $validated['has_certificate'] = $request->boolean('has_certificate');
        $validated['requires_quiz_pass'] = $request->boolean('requires_quiz_pass');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['currency'] = strtoupper($request->input('currency', 'GBP'));
        $validated['price'] = $validated['is_paid'] ? (float) ($validated['price'] ?? 0) : 0;

        $course->update($validated);
        AdminActivityService::log(auth('admin')->user(), 'course_update', $course, [], 'Updated course');

        return redirect()->route("admin.courses.index")->with('success', 'Course updated');
    }

    public function destroy(Course $course) {
        $course->delete();
        AdminActivityService::log(auth('admin')->user(), 'course_delete', $course, [], 'Deleted course');
        return back()->with('success', 'Course deleted');
    }
}
