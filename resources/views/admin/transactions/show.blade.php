@extends('admin.layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canViewUser = $admin && $admin->hasPermission('users.view'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Transaction {{ $transaction->reference }}</h1>
        <div class="d-flex gap-2">
            @if($canViewUser && $transaction->user)
                <a href="{{ route('admin.users.profile', $transaction->user) }}" class="btn btn-alt-primary">
                    <i class="fa fa-user me-1"></i> View User
                </a>
            @endif
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-alt-secondary">Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="block block-rounded h-100">
                <div class="block-content">
                    <h4 class="fs-5 mb-3">Summary</h4>
                    <p class="mb-2"><strong>Status:</strong> {{ strtoupper($transaction->status) }}</p>
                    <p class="mb-2"><strong>Gateway:</strong> {{ strtoupper($transaction->gateway) }}</p>
                    <p class="mb-2"><strong>Currency:</strong> {{ $transaction->currency }}</p>
                    <p class="mb-2"><strong>Subtotal:</strong> {{ number_format($transaction->subtotal, 2) }}</p>
                    <p class="mb-2"><strong>Tax:</strong> {{ number_format($transaction->tax_amount, 2) }}</p>
                    <p class="mb-2"><strong>Total:</strong> {{ number_format($transaction->total_amount, 2) }}</p>
                    <p class="mb-2"><strong>Paid At:</strong> {{ optional($transaction->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</p>
                    <p class="mb-2"><strong>Stripe Session:</strong><br><span class="text-muted">{{ $transaction->stripe_session_id ?: '-' }}</span></p>
                    <p class="mb-0"><strong>Payment Intent:</strong><br><span class="text-muted">{{ $transaction->stripe_payment_intent_id ?: '-' }}</span></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <h4 class="fs-5 mb-3">Purchased Items</h4>
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->items as $item)
                                    <tr>
                                        <td>{{ ucfirst($item->item_type) }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $transaction->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $transaction->currency }} {{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
