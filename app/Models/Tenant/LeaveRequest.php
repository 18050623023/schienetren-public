<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class LeaveRequest extends Model implements Ownable
{
    

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'notes',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($leaveRequest) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::LEAVE)) {
                throw new \Exception('Fitur Manajemen Cuti tidak aktif pada langganan Anda.');
            }
        });
    }

    // protected static function booted(): void
    // {
    //     static::creating(function ($leaveRequest) {
    //         $leaveType = $leaveRequest->leaveType;

    //         if (! $leaveType->requires_approval) {
    //             $leaveRequest->status = 'approved';
    //             $leaveRequest->approved_at = now();
    //         } else {
    //             $leaveRequest->status = 'pending';
    //         }
    //     });
    // }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function attachments()
    {
        return $this->hasMany(LeaveAttachment::class);
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from && $to) {
            return $query->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])
                  ->orWhereBetween('end_date', [$from, $to])
                  ->orWhere(function ($sub) use ($from, $to) {
                      $sub->where('start_date', '<=', $from)
                          ->where('end_date', '>=', $to);
                  });
            });
        }
        if ($from) {
            return $query->where('end_date', '>=', $from);
        }
        if ($to) {
            return $query->where('start_date', '<=', $to);
        }
        return $query;
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->whereHas('employee.employeeAssignments', fn($q) => $q->where('branch_id', $branchId));
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

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employee->user_id ?? null;
    }
}
