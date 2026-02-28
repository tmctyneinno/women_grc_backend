<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Course $course)
    {
        $modules = $course->modules()->paginate(10);

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
            'is_active' => 'boolean'
        ]);

        $course->modules()->create($validated);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module created successfully.');
    }

    public function edit(Course $course, Module $module)
    {
        return view('admin.modules.edit', compact('course', 'module'));
    }

    public function update(Request $request, Course $course, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $module->update($validated);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(Course $course, Module $module)
    {
        $module->delete();

        return back()->with('success', 'Module deleted.');
    }
}
