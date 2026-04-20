@extends('admin.layouts.app')

@section('title', 'Course Analytics')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">{{ $course->title }}</h3>
            <p class="text-muted mb-0">
                Modules: {{ $course->modules_count }} |
                Enrollments: {{ $course->enrollments_count }} |
                Price: {{ $course->is_paid ? ($course->currency . ' ' . number_format((float)$course->price, 2)) : 'Free' }}
            </p>
        </div>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-alt-secondary">Back to Courses</a>
    </div>

    <div class="block block-rounded mb-4">
        <div class="block-header">
            <h3 class="block-title">Enrollments</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Time Spent (mins)</th>
                        <th>Enrolled At</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($enrollments as $enrollment)
                    <tr>
                        <td>{{ $enrollment->user?->first_name }} {{ $enrollment->user?->last_name }}</td>
                        <td>
                            {{ $enrollment->user?->email }}
                            @if($canViewUser && $enrollment->user)
                                <a href="{{ route('admin.users.profile', $enrollment->user) }}" class="ms-2 text-muted" title="View User Profile">
                                    <i class="fa fa-user"></i>
                                </a>
                            @endif
                        </td>
                        <td>{{ ucfirst($enrollment->status) }}</td>
                        <td>{{ $enrollment->completion_percentage }}%</td>
                        <td>{{ $enrollment->time_spent_minutes }}</td>
                        <td>{{ optional($enrollment->enrolled_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No enrollments yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $enrollments->links() }}
        </div>
    </div>

    <div class="block block-rounded mb-4">
        <div class="block-header">
            <h3 class="block-title">Quiz Attempts</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Learner</th>
                        <th>Module</th>
                        <th>Score</th>
                        <th>Passed</th>
                        <th>Attempt #</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($quizAttempts as $attempt)
                    <tr>
                        <td>{{ $attempt->user?->first_name }} {{ $attempt->user?->last_name }}</td>
                        <td>{{ $attempt->module?->title }}</td>
                        <td>{{ $attempt->score }}%</td>
                        <td>
                            <span class="badge {{ $attempt->passed ? 'bg-success' : 'bg-danger' }}">
                                {{ $attempt->passed ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>{{ $attempt->attempt_number }}</td>
                        <td>{{ $attempt->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No quiz attempts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $quizAttempts->links() }}
        </div>
    </div>

    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Paid Purchases</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Learner</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Paid At</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->user?->first_name }} {{ $purchase->user?->last_name }}</td>
                        <td>{{ $purchase->user?->email }}</td>
                        <td>{{ $purchase->currency }} {{ number_format((float)$purchase->amount, 2) }}</td>
                        <td>{{ $purchase->payment_reference }}</td>
                        <td>{{ optional($purchase->paid_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No paid purchases yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $purchases->links() }}
        </div>
    </div>

    <div class="block block-rounded mt-4">
        <div class="block-header">
            <h3 class="block-title">Learning Points</h3>
        </div>
        <div class="block-content table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Learner</th>
                        <th>Email</th>
                        <th>Total Points</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($learningPoints as $row)
                    <tr>
                        <td>{{ $row->user?->first_name }} {{ $row->user?->last_name }}</td>
                        <td>{{ $row->user?->email }}</td>
                        <td>{{ (int) $row->total_points }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">No learning points yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $learningPoints->links() }}
        </div>
    </div>
</div>
@endsection
