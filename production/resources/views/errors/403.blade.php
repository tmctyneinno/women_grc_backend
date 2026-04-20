@extends('admin.layouts.app')

@section('title', '403 | Access Denied')

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="block block-rounded overflow-hidden">
                <div class="block-content block-content-full bg-body-light">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h1 class="h2 fw-bold mb-1">Access Denied</h1>
                            <p class="text-muted mb-0">You do not have permission to view this page.</p>
                        </div>
                        <div class="text-end">
                            <div class="fs-2 fw-bold text-danger">403</div>
                            <div class="text-uppercase text-muted fs-sm">Forbidden</div>
                        </div>
                    </div>
                </div>

                <div class="block-content">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-7">
                            <p class="mb-3">
                                If you believe this is a mistake, please contact the super admin to update
                                your role or permissions.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">
                                    <i class="fa fa-home me-1"></i> Go to Dashboard
                                </a>
                                <a class="btn btn-alt-secondary" href="{{ url()->previous() }}">
                                    <i class="fa fa-arrow-left me-1"></i> Go Back
                                </a>
                            </div>
                            <div class="text-muted fs-sm mt-3">
                                Tip: If your permissions changed recently, try logging out and back in.
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-4 bg-body rounded border text-center">
                                <div class="fs-1 fw-bold text-danger mb-1">403</div>
                                <div class="fw-semibold mb-2">Secure Area</div>
                                <div class="text-muted fs-sm">
                                    This action is restricted to authorized admin roles.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
