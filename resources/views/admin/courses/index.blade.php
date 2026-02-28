@extends('admin.layouts.app')

@section('title','Learning Center')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Courses</h3>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Create Course
            </a>
        </div>

        <div class="block-content table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Certificate</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>{{ $course->title }}</td>
                        <td>{{ $course->category ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $course->status === 'published' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td>
                            {{ $course->has_certificate ? 'Yes' : 'No' }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.courses.edit', ['course' => $course->id]) }}"
                            class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-pencil-alt"></i>
                            </a>


                            {{-- NEW: Manage Modules --}}
                            <a href="{{ route('admin.courses.modules.index', $course) }}" 
                            class="btn btn-sm btn-alt-primary"
                            title="Manage Modules">
                                <i class="fa fa-layer-group"></i>
                            </a>

                            <form method="POST" class="d-inline" action="{{ route('admin.courses.destroy',$course) }}"
                                  onsubmit="return confirm('Delete this course?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-alt-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No courses yet
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection