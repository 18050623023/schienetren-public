<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class FeatureToggle extends Model
{
    protected $table = 'tenant_feature_toggles';
    protected $fillable = ['feature_code', 'is_enabled'];
    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}