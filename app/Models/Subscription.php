<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'status', 'auto_renew', 'price', 'starts_at', 'ends_at', 'grace_period_ends_at'];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    
    public function subscriptionAddons()
    {
        return $this->hasMany(SubscriptionAddon::class);
    }
}