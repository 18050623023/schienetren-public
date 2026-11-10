<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class HrPayrollItem extends Model
{
    protected $guarded = ['id'];

    public function payroll()
    {
        return $this->belongsTo(HrPayroll::class, 'hr_payroll_id');
    }
}
