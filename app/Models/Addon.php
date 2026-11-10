<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['name', 'code', 'price', 'duration_in_days', 'grace_period_days', 'is_active'];

    public function addonFeatures()
    {
        return $this->hasMany(AddonFeature::class);
    }
}