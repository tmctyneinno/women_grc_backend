@extends('admin.layouts.app')

@section('title','Learning Center')

@section('content')
<div class="content">
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Total Courses</div>
                            <div class="fs-3 fw-semibold">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-body-light p-2">
                            <i class="fa fa-layer-group text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Published</div>
                            <div class="fs-3 fw-semibold text-success">{{ $stats['published'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-success-light p-2">
                            <i class="fa fa-check text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Draft</div>
                            <div class="fs-3 fw-semibold text-warning">{{ $stats['draft'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-warning-light p-2">
                            <i class="fa fa-pen text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="block block-rounded h-100">
                <div class="block-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-xs text-uppercase text-muted">Paid Courses</div>
                            <div class="fs-3 fw-semibold text-primary">{{ $stats['paid'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 bg-primary-light p-2">
                            <i class="fa fa-credit-card text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                        <th></th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Modules</th>
                        <th>Enrollments</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Certificate</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $course->title }}</td>
                        <td>
                            <span class="badge bg-body-light text-dark">
                                {{ $course->category ?? 'General' }}
                            </span>
                        </td>
                        <td>{{ $course->modules_count }}</td>
                        <td>{{ $course->enrollments_count }}</td>
                        <td>
                            @if($course->is_paid)
                                {{ $course->currency }} {{ number_format((float)$course->price, 2) }}
                            @else
                                Free
                            @endif
                        </td>
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

                            <a href="{{ route('admin.courses.show', ['course' => $course->id]) }}"
                               class="btn btn-sm btn-alt-success"
                               title="View Enrollments and Analytics">
                                <i class="fa fa-chart-bar"></i>
                            </a>

                            <a href="{{ route('admin.courses.modules.index', $course) }}"
                               class="btn btn-sm btn-alt-primary"
                               title="Manage Modules">
                                <i class="fa fa-layer-group"></i>
                            </a>

                            <form method="POST"
                                  class="d-inline"
                                  action="{{ route('admin.courses.destroy', $course) }}"
                                  onsubmit="return confirm('Delete this course?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-alt-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
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
