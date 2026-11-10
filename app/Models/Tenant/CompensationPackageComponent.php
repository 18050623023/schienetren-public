<?php

namespace App\Models\Tenant;

use App\Enums\CompensationPackageComponentBasedOn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompensationPackageComponent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'compensation_package_id',
        'compensation_component_id',
        'calc_type',
        'value',
        'formula',
        'based_on'
    ];

    public function casts(): array
    {
        return [
            'based_on' => CompensationPackageComponentBasedOn::class,
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CompensationPackage::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(CompensationComponent::class, 'compensation_component_id');
    }
}
