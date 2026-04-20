<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function all()
    {
        $quizzes = Quiz::query()
            ->with(['module:id,title,course_id', 'module.course:id,title'])
            ->latest()
            ->paginate(20);

        return view('admin.quizzes.all', compact('quizzes'));
    }

    public function index(Course $course, Module $module)
    {
        $this->ensureOwnership($course, $module);

        $quizzes = $module->quizzes()->latest()->paginate(10);

        return view('admin.quizzes.index', compact('course', 'module', 'quizzes'));
    }

    public function create(Course $course, Module $module)
    {
        $this->ensureOwnership($course, $module);

        return view('admin.quizzes.create', compact('course', 'module'));
    }

    public function store(Request $request, Course $course, Module $module)
    {
        $this->ensureOwnership($course, $module);

        $validated = $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'options' => 'nullable|string',
            'correct_answer' => 'required|string',
            'passing_threshold' => 'required|integer|min:1|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'show_feedback' => 'boolean',
        ]);

        $validated['options'] = $validated['options']
            ? array_values(array_filter(array_map('trim', explode(',', $validated['options']))))
            : [];
        $validated['show_feedback'] = $request->boolean('show_feedback');

        $module->quizzes()->create($validated);

        return redirect()
            ->route('admin.courses.modules.quizzes.index', [$course, $module])
            ->with('success', 'Quiz question created successfully.');
    }

    public function edit(Course $course, Module $module, Quiz $quiz)
    {
        $this->ensureOwnership($course, $module, $quiz);

        return view('admin.quizzes.edit', compact('course', 'module', 'quiz'));
    }

    public function update(Request $request, Course $course, Module $module, Quiz $quiz)
    {
        $this->ensureOwnership($course, $module, $quiz);

        $validated = $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'options' => 'nullable|string',
            'correct_answer' => 'required|string',
            'passing_threshold' => 'required|integer|min:1|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'show_feedback' => 'boolean',
        ]);

        $validated['options'] = $validated['options']
            ? array_values(array_filter(array_map('trim', explode(',', $validated['options']))))
            : [];
        $validated['show_feedback'] = $request->boolean('show_feedback');

        $quiz->update($validated);

        return redirect()
            ->route('admin.courses.modules.quizzes.index', [$course, $module])
            ->with('success', 'Quiz question updated successfully.');
    }

    public function destroy(Course $course, Module $module, Quiz $quiz)
    {
        $this->ensureOwnership($course, $module, $quiz);

        $quiz->delete();

        return back()->with('success', 'Quiz question deleted successfully.');
    }

    private function ensureOwnership(Course $course, Module $module, ?Quiz $quiz = null): void
    {
        abort_if((int) $module->course_id !== (int) $course->id, 404);
        if ($quiz) {
            abort_if((int) $quiz->module_id !== (int) $module->id, 404);
        }
    }
}
