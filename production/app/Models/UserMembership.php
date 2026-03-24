<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMembership extends Model
{
    protected $fillable = [
        'user_id',
        'membership_id',
        'membership_tier_id',
        'transaction_id',
        'status',
        'approval_status',
        'approved_at',
        'approved_by_admin_id',
        'started_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function tier()
    {
        return $this->belongsTo(MembershipTier::class, 'membership_tier_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
