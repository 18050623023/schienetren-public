<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class RepairTechnician extends Model implements Ownable
{
    protected $fillable = [
        'repair_device_id',
        'employee_assignment_id',
        'role',
        'compensation_component_id',
        'commission_rate',
        'commission_amount',
    ];

    public function repairDevice()
    {
        return $this->belongsTo(RepairDevice::class);
    }

    public function employeeAssignment()
    {
        return $this->belongsTo(EmployeeAssignment::class);
    }

    public function commissions()
    {
        return $this->hasMany(RepairTechnicianCommission::class, 'repair_technician_id');
    }

    public function compensationComponent()
    {
        return $this->belongsTo(CompensationComponent::class);
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employeeAssignment->employee->user_id ?? null;
    }
}
