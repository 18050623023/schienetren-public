<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\SkillEmployee;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class SkillEmployeePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:skill_employee');
    }

    public function view(authUser $authUser, SkillEmployee $skill_employee): bool
    {
        return $this->checkOwnership($authUser, 'view:skill_employee', $skill_employee);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:skill_employee');
    }

    public function update(authUser $authUser, SkillEmployee $skill_employee): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:skill_employee', $skill_employee);
    }

    public function delete(authUser $authUser, SkillEmployee $skill_employee): bool
    {
        return $this->checkOwnership($authUser, 'delete:skill_employee', $skill_employee);
    }

    public function restore(authUser $authUser, SkillEmployee $skill_employee): bool
    {
        return $this->checkOwnership($authUser, 'restore:skill_employee', $skill_employee);
    }

    public function forceDelete(authUser $authUser, SkillEmployee $skill_employee): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:skill_employee', $skill_employee);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:skill_employee');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:skill_employee');
    }

    public function replicate(authUser $authUser, SkillEmployee $skill_employee): bool
    {
        return $this->checkOwnership($authUser, 'replicate:skill_employee', $skill_employee);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:skill_employee');
    }

}
