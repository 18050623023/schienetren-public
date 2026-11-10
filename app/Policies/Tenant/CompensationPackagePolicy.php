<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\CompensationPackage;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class CompensationPackagePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:compensation_package');
    }

    public function view(authUser $authUser, CompensationPackage $compensation_package): bool
    {
        return $this->checkOwnership($authUser, 'view:compensation_package', $compensation_package);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:compensation_package');
    }

    public function update(authUser $authUser, CompensationPackage $compensation_package): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'update:compensation_package', $compensation_package);
    }

    public function delete(authUser $authUser, CompensationPackage $compensation_package): bool
    {
        return $this->checkOwnership($authUser, 'delete:compensation_package', $compensation_package);
    }

    public function restore(authUser $authUser, CompensationPackage $compensation_package): bool
    {
        return $this->checkOwnership($authUser, 'restore:compensation_package', $compensation_package);
    }

    public function forceDelete(authUser $authUser, CompensationPackage $compensation_package): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:compensation_package', $compensation_package);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:compensation_package');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:compensation_package');
    }

    public function replicate(authUser $authUser, CompensationPackage $compensation_package): bool
    {
        return $this->checkOwnership($authUser, 'replicate:compensation_package', $compensation_package);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:compensation_package');
    }

}
