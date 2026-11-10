<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompensationComponent extends Model
{
    use SoftDeletes ;

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    public function compensationPackages(): BelongsToMany
    {
        return $this->belongsToMany(
            CompensationPackage::class,
            'compensation_package_components'
        )
        ->withPivot([
            // 'id',
            'calc_type',
            'value',
            'formula',
            'based_on',
            'deleted_at',
        ])
        ->withTimestamps();
    }

    public function packageComponents(): HasMany
    {
        return $this->hasMany(CompensationPackageComponent::class);
    }

}
