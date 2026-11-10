<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\SkillSpecialization;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class SkillSpecializationPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:skill_specialization');
    }

    public function view(authUser $authUser, SkillSpecialization $skill_specialization): bool
    {
        return $this->checkOwnership($authUser, 'view:skill_specialization', $skill_specialization);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:skill_specialization');
    }

    public function update(authUser $authUser, SkillSpecialization $skill_specialization): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:skill_specialization', $skill_specialization);
    }

    public function delete(authUser $authUser, SkillSpecialization $skill_specialization): bool
    {
        return $this->checkOwnership($authUser, 'delete:skill_specialization', $skill_specialization);
    }

    public function restore(authUser $authUser, SkillSpecialization $skill_specialization): bool
    {
        return $this->checkOwnership($authUser, 'restore:skill_specialization', $skill_specialization);
    }

    public function forceDelete(authUser $authUser, SkillSpecialization $skill_specialization): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:skill_specialization', $skill_specialization);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:skill_specialization');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:skill_specialization');
    }

    public function replicate(authUser $authUser, SkillSpecialization $skill_specialization): bool
    {
        return $this->checkOwnership($authUser, 'replicate:skill_specialization', $skill_specialization);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:skill_specialization');
    }

}
