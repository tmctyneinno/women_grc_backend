<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    // ✅ Relationship: Membership has many tiers
    public function tiers()
    {
        return $this->hasMany(MembershipTier::class);
    }

    public function userMemberships()
    {
        return $this->hasMany(UserMembership::class);
    }
}
