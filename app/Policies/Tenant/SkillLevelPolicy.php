<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\SkillLevel;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class SkillLevelPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:skill_level');
    }

    public function view(authUser $authUser, SkillLevel $skill_level): bool
    {
        return $this->checkOwnership($authUser, 'view:skill_level', $skill_level);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:skill_level');
    }

    public function update(authUser $authUser, SkillLevel $skill_level): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:skill_level', $skill_level);
    }

    public function delete(authUser $authUser, SkillLevel $skill_level): bool
    {
        return $this->checkOwnership($authUser, 'delete:skill_level', $skill_level);
    }

    public function restore(authUser $authUser, SkillLevel $skill_level): bool
    {
        return $this->checkOwnership($authUser, 'restore:skill_level', $skill_level);
    }

    public function forceDelete(authUser $authUser, SkillLevel $skill_level): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:skill_level', $skill_level);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:skill_level');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:skill_level');
    }

    public function replicate(authUser $authUser, SkillLevel $skill_level): bool
    {
        return $this->checkOwnership($authUser, 'replicate:skill_level', $skill_level);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:skill_level');
    }

}
