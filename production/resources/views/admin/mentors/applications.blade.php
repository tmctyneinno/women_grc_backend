@extends('admin.layouts.app')

@section('title', 'Mentor Applications')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('mentors.update'))

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Mentor Applications</h3>
        </div>

        <div class="block-content table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Domain</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Details</th>
                        @if($canUpdate)
                            <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $application->user ? trim(($application->user->first_name ?? '') . ' ' . ($application->user->last_name ?? '')) : 'N/A' }}
                                </div>
                                <div class="small text-muted">{{ $application->user->email ?? '' }}</div>
                            </td>
                            <td>{{ $application->title }}</td>
                            <td>{{ $application->domain }}</td>
                            <td>{{ $application->region }}</td>
                            <td>
                                <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'declined' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </td>
                            <td>{{ optional($application->created_at)->format('M d, Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-alt-primary" data-bs-toggle="collapse" data-bs-target="#application-{{ $application->id }}">
                                    <i class="fa fa-eye"></i> View
                                </button>
                            </td>
                            @if($canUpdate)
                                <td class="text-end">
                                    @if($application->status === 'pending')
                                        <form action="{{ route('admin.mentors.applications.approve', $application) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-alt-success" onclick="return confirm('Approve this mentor application?')">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.mentors.applications.decline', $application) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-alt-danger" onclick="return confirm('Decline this mentor application?')">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">No actions</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                        <tr class="collapse-row">
                            <td colspan="{{ $canUpdate ? 9 : 8 }}" class="p-0">
                                <div id="application-{{ $application->id }}" class="collapse">
                                    <div class="application-card p-4">
                                        <div class="row g-3">
                                            <div class="col-lg-8">
                                                <div class="fw-semibold mb-2">Executive Summary</div>
                                                <div class="text-muted mb-3">
                                                    {{ $application->expertise_summary }}
                                                </div>

                                                <div class="fw-semibold mb-2">Bio</div>
                                                <div class="text-muted">
                                                    {{ $application->bio }}
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="info-box mb-3">
                                                    <div class="info-title fw-semibold">Availability</div>
                                                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $application->availability_status)) }}</div>
                                                </div>
                                                <div class="info-box mb-3">
                                                    <div class="info-title fw-semibold">Max Mentees</div>
                                                    <div class="info-value">{{ $application->max_mentees ?? 'Not specified' }}</div>
                                                </div>
                                                <div class="info-box">
                                                    <div class="info-title fw-semibold">Country</div>
                                                    <div class="info-value">{{ $application->country }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-2">
                                            <div class="col-md-4">
                                                <div class="tag-card">
                                                    <div class="fw-semibold mb-2">Languages</div>
                                                    <div class="tag-text">
                                                        {{ ($application->languages ?? []) ? implode(', ', $application->languages ?? []) : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="tag-card">
                                                    <div class="fw-semibold mb-2">Skills</div>
                                                    <div class="tag-text">
                                                        {{ ($application->skills ?? []) ? implode(', ', $application->skills ?? []) : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="tag-card">
                                                    <div class="fw-semibold mb-2">Certifications</div>
                                                    <div class="tag-text">
                                                        {{ ($application->certifications ?? []) ? implode(', ', $application->certifications ?? []) : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canUpdate ? 9 : 8 }}" class="text-center text-muted">No mentor applications yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($applications->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .application-card {
        background: #f9fbff;
        border-top: 1px solid #e1e8fb;
    }
    .info-box {
        border-radius: 12px;
        border: 1px solid #e3e9fb;
        background: #fff;
        padding: 12px;
    }
    .info-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #7a84a1;
        margin-bottom: 4px;
    }
    .info-value {
        font-weight: 600;
        color: #293567;
    }
    .tag-card {
        border-radius: 12px;
        border: 1px solid #e3e9fb;
        background: #fff;
        padding: 12px;
        height: 100%;
    }
    .tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .tag-pill {
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        background: #edf2ff;
        color: #2f4384;
        border: 1px solid #d6e1ff;
    }
    .tag-text {
        color: #2f3b5f;
        font-size: 14px;
        line-height: 1.6;
    }
</style>
@endpush
