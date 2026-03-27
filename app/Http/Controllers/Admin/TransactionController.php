<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Event;
use App\Models\MembershipTier;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $admin = auth('admin')->user();

        $transactions = Transaction::with(['user', 'items'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($admin && !$admin->isSuperAdmin(), function ($query) use ($admin) {
                $this->applyTransactionPermissionScope($query, $admin);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'status'));
    }

    public function show(Transaction $transaction)
    {
        $admin = auth('admin')->user();

        if ($admin && !$admin->isSuperAdmin()) {
            $scoped = Transaction::with(['user', 'items'])->whereKey($transaction->id);
            $this->applyTransactionPermissionScope($scoped, $admin);
            $transaction = $scoped->firstOrFail();
        } else {
            $transaction->load(['user', 'items']);
        }

        return view('admin.transactions.show', compact('transaction'));
    }

    private function applyTransactionPermissionScope($query, $admin)
    {
        $itemMap = $this->getAllowedTransactionItemMap($admin);
        $allowedTypes = array_keys(array_filter($itemMap, fn ($ids) => !empty($ids)));

        if (empty($allowedTypes)) {
            return $query->whereRaw('1=0');
        }

        $query->whereHas('items', function ($q) use ($itemMap) {
            $q->where(function ($or) use ($itemMap) {
                foreach ($itemMap as $type => $ids) {
                    if (empty($ids)) {
                        continue;
                    }
                    $or->orWhere(function ($sub) use ($type, $ids) {
                        $sub->where('item_type', $type)
                            ->whereIn('item_id', $ids);
                    });
                }
            });
        });

        $query->whereDoesntHave('items', function ($q) use ($itemMap, $allowedTypes) {
            $q->where(function ($inner) use ($itemMap, $allowedTypes) {
                $inner->whereNotIn('item_type', $allowedTypes);
                foreach ($itemMap as $type => $ids) {
                    if (empty($ids)) {
                        continue;
                    }
                    $inner->orWhere(function ($sub) use ($type, $ids) {
                        $sub->where('item_type', $type)
                            ->whereNotIn('item_id', $ids);
                    });
                }
            });
        });

        return $query;
    }

    private function getAllowedTransactionItemMap($admin): array
    {
        $map = [];

        if ($admin->hasPermission('events')) {
            $map['event'] = Event::where('created_by', $admin->id)->pluck('id')->all();
        }

        if ($admin->hasPermission('courses')) {
            $map['course'] = Course::where('created_by', $admin->id)->pluck('id')->all();
        }

        if ($admin->hasPermission('memberships')) {
            $map['membership'] = MembershipTier::where('created_by', $admin->id)->pluck('id')->all();
        }

        return $map;
    }
}
