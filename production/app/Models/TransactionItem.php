<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'item_type',
        'item_id',
        'title',
        'unit_price',
        'quantity',
        'line_total',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

