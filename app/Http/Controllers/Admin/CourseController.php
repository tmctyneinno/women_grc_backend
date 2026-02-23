<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;

class CourseController extends Controller
{
    public function index() {
        $courses = Course::latest()->paginate(10);
        return view('admin.courses.index', compact('courses'));
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
            'has_certificate' => 'boolean'
        ]);

        $validated['tags'] = $request->tags ? explode(',', $request->tags) : [];

        Course::create($validated);

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
            'status' => 'required|in:draft,published'
        ]);

        $validated['tags'] = $request->tags ? explode(',', $request->tags) : [];

        $course->update($validated);

        return back()->with('success', 'Course updated');
    }

    public function destroy(Course $course) {
        $course->delete();
        return back()->with('success', 'Course deleted');
    }
}
