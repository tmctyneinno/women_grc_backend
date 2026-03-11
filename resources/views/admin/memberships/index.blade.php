@extends('admin.layouts.app')

@section('content')
<div class="content">
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
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingMemberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->first_name }} {{ $membership->user?->last_name }}</td>
                                    <td>{{ $membership->user?->email }}</td>
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
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.memberships.approve', $membership->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                        </form>
                                    </td>
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
