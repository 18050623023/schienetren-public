<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleDay extends Model
{


    protected $fillable = [
        'work_schedule_id',
        'work_shift_id',
        'day_of_week',
        'is_working_days',
    ];

    protected $casts = [
        'is_working_days' => 'boolean',
    ];

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

}
