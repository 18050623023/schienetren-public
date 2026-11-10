<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationAssignment extends Model implements Ownable
{

    protected $fillable = [
        'employee_assignment_id',
        'compensation_package_id',
        'start_date',
        'end_date',
    ];

    public function assignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CompensationPackage::class);
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
