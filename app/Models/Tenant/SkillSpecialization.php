<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SkillSpecialization extends Model
{
    protected $fillable = [
        'name', //Hardware, Software, Network
    ];

    protected static function booted(): void
    {
        static::creating(function ($skillSpecialization) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::SKILL_MANAGEMENT)) {
                throw new \Exception('Fitur Manajemen Skill Teknisi tidak aktif pada langganan Anda.');
            }
        });
    }

    public function skill()
    {
        return $this->hasMany(SkillEmployee::class);
    }
}
