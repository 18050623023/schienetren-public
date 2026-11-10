<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class HrPayroll extends Model
{
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::observe(\App\Observers\Tenant\HrPayrollObserver::class);

        static::creating(function ($payroll) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::PAYROLL)) {
                throw new \Exception('Fitur Penggajian & Kompensasi tidak aktif pada langganan Anda.');
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Tenant\Employee::class);
    }

    public function compensationPackage()
    {
        return $this->belongsTo(\App\Models\Tenant\CompensationPackage::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\Tenant\PaymentMethod::class);
    }

    public function items()
    {
        return $this->hasMany(HrPayrollItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Tenant\Branch::class);
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->where('branch_id', $branchId);
    }

    public function scopeForPeriod($query, $period)
    {
        if (!$period) {
            return $query;
        }
        return $query->where('period', $period);
    }

    public function scopeBetweenPeriods($query, $startPeriod, $endPeriod)
    {
        if ($startPeriod && $endPeriod) {
            return $query->whereBetween('period', [$startPeriod, $endPeriod]);
        }
        if ($startPeriod) {
            return $query->where('period', '>=', $startPeriod);
        }
        if ($endPeriod) {
            return $query->where('period', '<=', $endPeriod);
        }
        return $query;
    }

    public function scopeForEmployee($query, $employeeId)
    {
        if (!$employeeId) {
            return $query;
        }
        return $query->where('employee_id', $employeeId);
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }
}
