<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;
use Spatie\Permission\PermissionRegistrar;

class EmployeeRoleAssignment extends Model implements Ownable
{
    

    // protected $fillable = [
    //     'employee_assignment_id',
    //     app(PermissionRegistrar::class)->pivotRole,
    // ];

    public function getFillable()
    {
        return [
            'employee_assignment_id',
            app(PermissionRegistrar::class)->pivotRole,
        ];
    }

    public function employeeAssignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    public function role()
    {
        return $this->belongsTo(config('permission.models.role'));
    }

    // public function employeeRole()
    // {
    //     return $this->belongsTo(EmployeeRole::class);
    // }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
