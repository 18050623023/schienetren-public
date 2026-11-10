<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompensationPackage extends Model
{

    use SoftDeletes ;

    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::creating(function ($package) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::PAYROLL)) {
                throw new \Exception('Fitur Penggajian & Kompensasi tidak aktif pada langganan Anda.');
            }
        });
    }



    public function compensationComponents(): BelongsToMany
    {
        return $this->belongsToMany(
            CompensationComponent::class,
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

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(
            EmployeeAssignment::class,
            'compensation_assignments',
            // 'compensation_package_id',
            // 'employee_assignment_id',
        )
            ->withPivot([
                'start_date',
                'end_date',
            ])
            ->withTimestamps();
    }

    public function assignmentsTest(): HasMany
    {
        return $this->hasMany(CompensationAssignment::class);
    }
    // public function assignments(): HasMany
    // {
    //     return $this->hasMany(CompensationAssignment::class);
    // }
}
