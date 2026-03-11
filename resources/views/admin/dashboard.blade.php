@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Hero -->
<div class="content">
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
        <div class="flex-grow-1 mb-1 mb-md-0">
            <h1 class="h3 fw-bold mb-2">Dashboard</h1>
            <h2 class="h6 fw-medium text-muted mb-0">
                Welcome <a class="fw-semibold" href="#">{{ auth()->user()->name ?? 'Admin' }}</a>, everything looks great.
            </h2>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">
    <!-- Overview -->
    <div class="row items-push">

        <!-- Total Users -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $totalUsers }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Total Users</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <a href="{{ route('admin.users.index') }}">
                            <i class="far fa-user fs-3 text-primary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Users -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $pendingUsers }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Pending Users</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-clock fs-3 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Events -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $totalEvents }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Total Events</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-calendar fs-3 text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $upcomingEvents }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Upcoming Events</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-calendar-check fs-3 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Bookings -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $totalBookings }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Total Bookings</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-ticket-alt fs-3 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmed Bookings -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $confirmedBookings }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Confirmed Bookings</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-check-circle fs-3 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Courses -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $totalCourses }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Total Courses</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-book fs-3 text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocked Users -->
        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $blockedUsers }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Blocked Users</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-ban fs-3 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">{{ $totalTransactions }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Total Transactions</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <a href="{{ route('admin.transactions.index') }}">
                            <i class="fa fa-receipt fs-3 text-warning"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xxl-3">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <dl class="mb-0">
                        <dt class="fs-3 fw-bold">GBP {{ number_format($totalRevenueGbp, 2) }}</dt>
                        <dd class="fs-sm fw-medium text-muted mb-0">Paid Revenue</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-pound-sign fs-3 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- END Overview -->

    <!-- Statistics --> 
    <div class="row">
    <div class="col-xl-8 col-xxl-9 d-flex flex-column">
        <!-- Earnings Summary -->
        <div class="block block-rounded flex-grow-1 d-flex flex-column">
        <div class="block-header block-header-default">
            <h3 class="block-title">Earnings Summary</h3>
            <div class="block-options">
            <button type="button" class="btn-block-option" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                <i class="si si-refresh"></i>
            </button>
            <button type="button" class="btn-block-option">
                <i class="si si-settings"></i>
            </button>
            </div>
        </div>
        <div class="block-content block-content-full flex-grow-1 d-flex align-items-center">
            <!-- Earnings Chart Container -->
            <!-- Chart.js Chart is initialized in js/pages/be_pages_dashboard.min.js which was auto compiled from _js/pages/be_pages_dashboard.js -->
            <!-- For more info and examples you can check out http://www.chartjs.org/docs/ -->
            <canvas id="js-chartjs-earnings"></canvas>
        </div>
        <div class="block-content bg-body-light">
            <div class="row items-push text-center w-100">
            <div class="col-sm-4">
                <dl class="mb-0">
                <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                    <i class="fa fa-caret-up fs-base text-success"></i>
                    <span>2.5%</span>
                </dt>
                <dd class="fs-sm fw-medium text-muted mb-0">Customer Growth</dd>
                </dl>
            </div>
            <div class="col-sm-4">
                <dl class="mb-0">
                <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                    <i class="fa fa-caret-up fs-base text-success"></i>
                    <span>3.8%</span>
                </dt>
                <dd class="fs-sm fw-medium text-muted mb-0">Page Views</dd>
                </dl>
            </div>
            <div class="col-sm-4">
                <dl class="mb-0">
                <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                    <i class="fa fa-caret-down fs-base text-danger"></i>
                    <span>1.7%</span>
                </dt>
                <dd class="fs-sm fw-medium text-muted mb-0">New Products</dd>
                </dl>
            </div>
            </div>
        </div>
        </div>
        <!-- END Earnings Summary -->
    </div>
    <div class="col-xl-4 col-xxl-3 d-flex flex-column">
        <!-- Last 2 Weeks -->
        <!-- Chart.js Charts is initialized in js/pages/be_pages_dashboard.min.js which was auto compiled from _js/pages/be_pages_dashboard.js -->
        <!-- For more info and examples you can check out http://www.chartjs.org/docs/ -->
        <div class="row items-push flex-grow-1">
        <div class="col-md-6 col-xl-12">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
            <div class="block-content flex-grow-1 d-flex justify-content-between">
                <dl class="mb-0">
                <dt class="fs-3 fw-bold">570</dt>
                <dd class="fs-sm fw-medium text-muted mb-0">Total Orders</dd>
                </dl>
                <div>
                <div class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold text-danger bg-danger-light">
                    <i class="fa fa-caret-down me-1"></i>
                    2.2%
                </div>
                </div>
            </div>
            <div class="block-content p-1 text-center overflow-hidden">
                <!-- Total Orders Chart Container -->
                <canvas id="js-chartjs-total-orders" style="height: 90px;"></canvas>
            </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-12">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
            <div class="block-content flex-grow-1 d-flex justify-content-between">
                <dl class="mb-0">
                <dt class="fs-3 fw-bold">$5,234.21</dt>
                <dd class="fs-sm fw-medium text-muted mb-0">Total Earnings</dd>
                </dl>
                <div>
                <div class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold text-success bg-success-light">
                    <i class="fa fa-caret-up me-1"></i>
                    4.2%
                </div>
                </div>
            </div>
            <div class="block-content p-1 text-center overflow-hidden">
                <!-- Total Earnings Chart Container -->
                <canvas id="js-chartjs-total-earnings" style="height: 90px;"></canvas>
            </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
            <div class="block-content flex-grow-1 d-flex justify-content-between">
                <dl class="mb-0">
                <dt class="fs-3 fw-bold">264</dt>
                <dd class="fs-sm fw-medium text-muted mb-0">New Customers</dd>
                </dl>
                <div>
                <div class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold text-success bg-success-light">
                    <i class="fa fa-caret-up me-1"></i>
                    9.3%
                </div>
                </div>
            </div>
            <div class="block-content p-1 text-center overflow-hidden">
                <!-- New Customers Chart Container -->
                <canvas id="js-chartjs-new-customers" style="height: 90px;"></canvas>
            </div>
            </div>
        </div>
        </div>
        <!-- END Last 2 Weeks -->
    </div>
    </div>
    <!-- END Statistics -->

    <!-- Recent Transactions -->
    <div class="block block-rounded">
    <div class="block-header block-header-default">
        <h3 class="block-title">Recent Transactions</h3>
        <div class="block-options">
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-alt-secondary">View All</a>
        </div>
    </div>
    <div class="block-content block-content-full">
        <div class="table-responsive">
        <table class="table table-hover table-vcenter">
            <thead>
            <tr>
                <th>Reference</th>
                <th class="d-none d-xl-table-cell">Customer</th>
                <th>Status</th>
                <th class="d-none d-sm-table-cell text-end">Created</th>
                <th class="d-none d-sm-table-cell text-end">Amount</th>
            </tr>
            </thead>
            <tbody class="fs-sm">
            @forelse($recentTransactions as $tx)
                <tr>
                    <td>
                        <a class="fw-semibold" href="{{ route('admin.transactions.show', $tx) }}">{{ $tx->reference }}</a>
                        <p class="fs-sm fw-medium text-muted mb-0">{{ strtoupper($tx->gateway) }}</p>
                    </td>
                    <td class="d-none d-xl-table-cell">
                        <a class="fw-semibold" href="javascript:void(0)">{{ $tx->user?->first_name }} {{ $tx->user?->last_name }}</a>
                        <p class="fs-sm fw-medium text-muted mb-0">{{ $tx->user?->email }}</p>
                    </td>
                    <td>
                        <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill
                            {{ $tx->status === 'paid' ? 'bg-success-light text-success' : ($tx->status === 'failed' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning') }}">
                            {{ strtoupper($tx->status) }}
                        </span>
                    </td>
                    <td class="d-none d-sm-table-cell fw-semibold text-muted text-end">{{ $tx->created_at->diffForHumans() }}</td>
                    <td class="d-none d-sm-table-cell text-end">
                        <strong>{{ $tx->currency }} {{ number_format($tx->total_amount, 2) }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No transactions yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
    </div>
    <!-- END Recent Transactions -->
</div>
<!-- END Page Content -->
@endsection
