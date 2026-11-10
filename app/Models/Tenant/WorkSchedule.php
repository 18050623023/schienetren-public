<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSchedule extends Model
{
    // use SoftDeletes;



    protected $fillable = ['name','is_active']; //teknisi siang, admin malam

    public static bool $isCreatingDefault = false;

    public static function getDefaultSchedule(): ?self
    {
        $schedule = static::query()->where('name', 'Jadwal Reguler (Standar)')->first()
            ?? static::query()->where('is_active', true)->first()
            ?? static::query()->first();

        if ($schedule) {
            return $schedule;
        }

        static::$isCreatingDefault = true;
        WorkShift::$isCreatingDefault = true;
        try {
            $shift = WorkShift::firstOrCreate(
                ['name' => 'Shift Reguler'],
                [
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                    'break_minutes' => 60,
                    'late_tolerance' => 15,
                    'overtime_tolerance' => 30,
                ]
            );

            $schedule = static::create([
                'name' => 'Jadwal Reguler (Standar)',
                'is_active' => true,
            ]);

            for ($day = 1; $day <= 7; $day++) {
                WorkScheduleDay::create([
                    'work_schedule_id' => $schedule->id,
                    'work_shift_id' => $shift->id,
                    'day_of_week' => $day,
                    'is_working_days' => $day <= 6,
                ]);
            }

            return $schedule;
        } finally {
            static::$isCreatingDefault = false;
            WorkShift::$isCreatingDefault = false;
        }
    }

    protected static function booted(): void
    {
        static::creating(function (WorkSchedule $schedule) {
            if (static::$isCreatingDefault) {
                return;
            }

            if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::SHIFT_MANAGEMENT)) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['feature' => 'Fitur Shift Management tidak aktif pada paket tenant ini.']
                );
            }
        });
    }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeAssignment::class);
    }


    public function days()
    {
        return $this->hasMany(WorkScheduleDay::class);
    }

    public function workScheduleToday()
    {
        return $this->hasOne(WorkScheduleDay::class)
            ->where('day_of_week', now()->dayOfWeekIso);
    }

}
