<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionAddon extends Model
{
    protected $fillable = ['subscription_id', 'tenant_id', 'addon_id', 'qty', 'auto_renew', 'price', 'starts_at', 'ends_at', 'grace_period_ends_at'];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}