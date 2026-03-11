<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'expires_at'];

    protected $dates = ['expires_at'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }
}