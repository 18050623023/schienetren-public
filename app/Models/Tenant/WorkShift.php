<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    public static bool $isCreatingDefault = false;
    
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_minutes',
        'late_tolerance',
        'overtime_tolerance',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkShift $shift) {
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

    public function scheduleDays()
    {
        return $this->hasMany(WorkScheduleDay::class);
    }
}
