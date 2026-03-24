<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $transactions = Transaction::with(['user', 'items'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'status'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'items']);

        return view('admin.transactions.show', compact('transaction'));
    }
}

