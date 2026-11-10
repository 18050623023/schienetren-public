<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\CompensationComponent;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class CompensationComponentPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:compensation_component');
    }

    public function view(authUser $authUser, CompensationComponent $compensation_component): bool
    {
        return $this->checkOwnership($authUser, 'view:compensation_component', $compensation_component);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:compensation_component');
    }

    public function update(authUser $authUser, CompensationComponent $compensation_component): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:compensation_component', $compensation_component);
    }

    public function delete(authUser $authUser, CompensationComponent $compensation_component): bool
    {
        return $this->checkOwnership($authUser, 'delete:compensation_component', $compensation_component);
    }

    public function restore(authUser $authUser, CompensationComponent $compensation_component): bool
    {
        return $this->checkOwnership($authUser, 'restore:compensation_component', $compensation_component);
    }

    public function forceDelete(authUser $authUser, CompensationComponent $compensation_component): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:compensation_component', $compensation_component);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:compensation_component');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:compensation_component');
    }

    public function replicate(authUser $authUser, CompensationComponent $compensation_component): bool
    {
        return $this->checkOwnership($authUser, 'replicate:compensation_component', $compensation_component);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:compensation_component');
    }

}
