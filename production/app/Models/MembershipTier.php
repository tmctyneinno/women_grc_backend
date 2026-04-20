<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class MembershipTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'name',
        'annual_fee',
        'target_audience',
        'benefits', 
        'invitation_only',
        'created_by',
    ];

    protected $casts = [
        'benefits' => 'array'
    ];

    // ✅ Relationship: Tier belongs to a Membership
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function userMemberships()
    {
        return $this->hasMany(UserMembership::class, 'membership_tier_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
