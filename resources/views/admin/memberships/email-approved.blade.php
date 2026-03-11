@extends('admin.layouts.app')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-content text-center py-5">
            <h3 class="mb-3">Membership Approved</h3>
            <p class="text-muted mb-0">
                Membership #{{ $userMembership->id }} has been approved successfully.
            </p>
        </div>
    </div>
</div>
@endsection
