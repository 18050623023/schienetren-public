<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\Ownable;

class SkillEmployee extends Model implements Ownable
{


    protected $fillable = [
        'employee_id',
        'skill_specialization_id',
        'skill_level_id',
    ];

    protected static function booted(): void
    {
        static::creating(function ($skillEmployee) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::SKILL_MANAGEMENT)) {
                throw new \Exception('Fitur Manajemen Skill Teknisi tidak aktif pada langganan Anda.');
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function skillSpecialization()
    {
        return $this->belongsTo(SkillSpecialization::class);
    }

    public function skillLevel()
    {
        return $this->belongsTo(SkillLevel::class);
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->employee->user_id ?? null;
    }
}
