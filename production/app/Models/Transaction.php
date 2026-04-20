<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'cart_id',
        'reference',
        'gateway',
        'currency',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}

