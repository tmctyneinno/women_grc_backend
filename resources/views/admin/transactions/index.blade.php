@extends('admin.layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canViewTx = $admin && $admin->hasPermission('transactions.view'))
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Transactions</h1>
    </div>

    <div class="block block-rounded">
        <div class="block-content block-content-full">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded'] as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Paid At</th>
                            @if($canViewTx)
                                <th class="text-end">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ $tx->reference }}</td>
                                <td>
                                    {{ $tx->user?->first_name }} {{ $tx->user?->last_name }}
                                    @if($canViewUser && $tx->user)
                                        <a href="{{ route('admin.users.profile', $tx->user) }}" class="ms-1 text-muted" title="View User Profile">
                                            <i class="fa fa-user"></i>
                                        </a>
                                    @endif
                                    <br>
                                    <span class="text-muted fs-sm">{{ $tx->user?->email }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $tx->status === 'paid' ? 'bg-success' : ($tx->status === 'failed' ? 'bg-danger' : 'bg-warning') }}">
                                        {{ strtoupper($tx->status) }}
                                    </span>
                                </td>
                                <td>{{ $tx->items->count() }}</td>
                                <td>{{ $tx->currency }} {{ number_format($tx->total_amount, 2) }}</td>
                                <td>{{ optional($tx->paid_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                @if($canViewTx)
                                    <td class="text-end">
                                        <a href="{{ route('admin.transactions.show', $tx) }}" class="btn btn-sm btn-alt-primary">View</a>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canViewTx ? 7 : 6 }}" class="text-center text-muted">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
