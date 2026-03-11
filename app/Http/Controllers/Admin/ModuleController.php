<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function all()
    {
        $modules = Module::with('course:id,title')
            ->withCount(['lessons', 'quizzes'])
            ->latest()
            ->paginate(20);

        return view('admin.modules.all', compact('modules'));
    }

    public function index(Course $course)
    {
        $modules = $course->modules()->withCount(['lessons', 'quizzes'])->paginate(10);

        return view('admin.modules.index', compact('course', 'modules'));
    }

    public function create(Course $course)
    {
        return view('admin.modules.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'require_quiz_to_unlock' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['require_quiz_to_unlock'] = $request->boolean('require_quiz_to_unlock');

        $course->modules()->create($validated);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module created successfully.');
    }

    public function edit(Course $course, Module $module)
    {
        abort_if((int) $module->course_id !== (int) $course->id, 404);
        return view('admin.modules.edit', compact('course', 'module'));
    }

    public function update(Request $request, Course $course, Module $module)
    {
        abort_if((int) $module->course_id !== (int) $course->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'require_quiz_to_unlock' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['require_quiz_to_unlock'] = $request->boolean('require_quiz_to_unlock');

        $module->update($validated);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(Course $course, Module $module)
    {
        abort_if((int) $module->course_id !== (int) $course->id, 404);
        $module->delete();

        return back()->with('success', 'Module deleted.');
    }
}
