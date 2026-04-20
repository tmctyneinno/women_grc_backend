@extends('admin.layouts.app')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canApprove = $admin && $admin->hasPermission('memberships.approve'))
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Pending Membership Approvals</h3>
        </div>
        <div class="block-content">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($pendingMemberships->isEmpty())
                <div class="alert alert-info">No pending memberships.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-vcenter">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>LinkedIn</th>
                                <th>Membership</th>
                                <th>Tier</th>
                                <th>Requested</th>
                                @if($canApprove)
                                    <th class="text-end">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingMemberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->first_name }} {{ $membership->user?->last_name }}</td>
                                    <td>
                                        {{ $membership->user?->email }}
                                        @if($canViewUser && $membership->user)
                                            <a href="{{ route('admin.users.profile', $membership->user) }}" class="ms-2 text-muted" title="View User Profile">
                                                <i class="fa fa-user"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($membership->user?->linkedin_profile)
                                            <a href="{{ $membership->user->linkedin_profile }}" target="_blank" rel="noopener">View</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $membership->membership?->name }}</td>
                                    <td>{{ $membership->tier?->name }}</td>
                                    <td>{{ optional($membership->created_at)->format('Y-m-d H:i') }}</td>
                                    @if($canApprove)
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.memberships.approve', $membership->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $pendingMemberships->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
