@extends('admin.layouts.app')

@section('title', 'Membership Tiers')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canUpdate = $admin && $admin->hasPermission('memberships.update'))
    @php($canDelete = $admin && $admin->hasPermission('memberships.delete'))
    @php($canSeeActions = $admin && ($canUpdate || $canDelete))
    <div class="block block-rounded">
        <div class="block-header">
            <div>
                <h3 class="block-title">Tiers for {{ $membership->name }}</h3>
                <div class="text-muted small">{{ $membership->description }}</div>
            </div>
            <div class="d-flex gap-2">
                @if($canUpdate)
                    <a href="{{ route('admin.membership-plans.tiers.create', $membership) }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Tier
                    </a>
                @endif
                <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-alt-secondary">Back</a>
            </div>
        </div>

        <div class="block-content table-responsive">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tier Name</th>
                        <th>Annual Fee</th>
                        <th>Target Audience</th>
                        <th>Invitation Only</th>
                        <th>Benefits</th>
                        @if($canSeeActions)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($tiers as $tier)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tier->name }}</td>
                        <td>&pound;{{ number_format((float) $tier->annual_fee, 2) }}</td>
                        <td>{{ $tier->target_audience }}</td>
                        <td>{{ $tier->invitation_only ?: 'No' }}</td>
                        <td>
                            @if(is_array($tier->benefits))
                                <ul class="mb-0">
                                    @foreach($tier->benefits as $benefit)
                                        <li>{{ $benefit }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ $tier->benefits }}
                            @endif
                        </td>
                        @if($canSeeActions)
                            <td class="text-center">
                                @if($canUpdate)
                                    <a href="{{ route('admin.membership-plans.tiers.edit', [$membership, $tier]) }}" class="btn btn-sm btn-alt-secondary">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                @endif
                                @if($canDelete)
                                    <form method="POST" class="d-inline" action="{{ route('admin.membership-plans.tiers.destroy', [$membership, $tier]) }}" onsubmit="return confirm('Delete this tier?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-alt-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canSeeActions ? 7 : 6 }}" class="text-center text-muted py-4">No tiers yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $tiers->links() }}
        </div>
    </div>
</div>
@endsection
