<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class FeatureLimit extends Model
{
    protected $table = 'tenant_feature_limits';
    protected $fillable = ['feature_code', 'feature_type', 'max_value', 'is_expired', 'synced_at'];
    protected $casts = [
        'is_expired' => 'boolean',
        'synced_at' => 'datetime',
    ];
}