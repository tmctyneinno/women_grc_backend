<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function all()
    {
        $lessons = Lesson::query()
            ->with(['module:id,title,course_id', 'module.course:id,title'])
            ->latest()
            ->paginate(20);

        return view('admin.lessons.all', compact('lessons'));
    }

    public function index(Course $course, Module $module)
    {
        $this->ensureOwnership($course, $module);

        $lessons = $module->lessons()->paginate(10);

        return view('admin.lessons.index', compact('course', 'module', 'lessons'));
    }

    public function create(Course $course, Module $module)
    {
        $this->ensureOwnership($course, $module);

        return view('admin.lessons.create', compact('course', 'module'));
    }

    public function store(Request $request, Course $course, Module $module)
    {
        $this->ensureOwnership($course, $module);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,video,file',
            'content' => 'nullable|string',
            'file_path' => 'nullable|string|max:255',
            'uploaded_file' => 'nullable|file|max:20480',
            'position' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('uploaded_file')) {
            $storedPath = $request->file('uploaded_file')->store('lessons', 'public');
            $validated['file_path'] = url(Storage::disk('public')->url($storedPath));
        }

        $module->lessons()->create($validated);

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson created successfully.');
    }

    public function edit(Course $course, Module $module, Lesson $lesson)
    {
        $this->ensureOwnership($course, $module, $lesson);

        return view('admin.lessons.edit', compact('course', 'module', 'lesson'));
    }

    public function update(Request $request, Course $course, Module $module, Lesson $lesson)
    {
        $this->ensureOwnership($course, $module, $lesson);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,video,file',
            'content' => 'nullable|string',
            'file_path' => 'nullable|string|max:255',
            'uploaded_file' => 'nullable|file|max:20480',
            'position' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('uploaded_file')) {
            $storedPath = $request->file('uploaded_file')->store('lessons', 'public');
            $validated['file_path'] = url(Storage::disk('public')->url($storedPath));
        }

        $lesson->update($validated);

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, Module $module, Lesson $lesson)
    {
        $this->ensureOwnership($course, $module, $lesson);

        $lesson->delete();

        return back()->with('success', 'Lesson deleted successfully.');
    }

    private function ensureOwnership(Course $course, Module $module, ?Lesson $lesson = null): void
    {
        abort_if((int) $module->course_id !== (int) $course->id, 404);
        if ($lesson) {
            abort_if((int) $lesson->module_id !== (int) $module->id, 404);
        }
    }
}
