<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SkillLevel extends Model
{
    

    protected $fillable = [
        'name', // Junior, Senior, Advance
    ];

    protected static function booted(): void
    {
        static::creating(function ($skillLevel) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::SKILL_MANAGEMENT)) {
                throw new \Exception('Fitur Manajemen Skill Teknisi tidak aktif pada langganan Anda.');
            }
        });
    }

    public function skillEmployees()
    {
        return $this->hasMany(SkillEmployee::class);
    }
}
