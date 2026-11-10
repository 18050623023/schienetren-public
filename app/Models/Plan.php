<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'slug', 'price', 'duration_in_days', 'grace_period_days', 'is_active'];

    public function planFeatures()
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }


    /**
     * Get feature value by feature enum
     * @param \App\Enums\TenantFeature $feature
     * @return mixed
     */
    public function getFeatureValue(\App\Enums\TenantFeature $feature): mixed
    {
        $planFeature = $this->planFeatures()
            ->whereHas('feature', function ($query) use ($feature) {
                $query->where('code', $feature->value);
            })
            ->first();

        return $planFeature ? $planFeature->value : null;
    }
}