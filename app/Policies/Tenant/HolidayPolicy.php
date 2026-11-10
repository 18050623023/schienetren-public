<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Holiday;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class HolidayPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::HOLIDAY)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:holiday');
    }

    public function view(authUser $authUser, Holiday $holiday): bool
    {
        return $this->checkOwnership($authUser, 'view:holiday', $holiday);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::HOLIDAY)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:holiday');
    }

    public function update(authUser $authUser, Holiday $holiday): bool
    {
        return $this->checkOwnership($authUser, 'update:holiday', $holiday);
    }

    public function delete(authUser $authUser, Holiday $holiday): bool
    {
        return $this->checkOwnership($authUser, 'delete:holiday', $holiday);
    }

    public function restore(authUser $authUser, Holiday $holiday): bool
    {
        return $this->checkOwnership($authUser, 'restore:holiday', $holiday);
    }

    public function forceDelete(authUser $authUser, Holiday $holiday): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:holiday', $holiday);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:holiday');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:holiday');
    }

    public function replicate(authUser $authUser, Holiday $holiday): bool
    {
        return $this->checkOwnership($authUser, 'replicate:holiday', $holiday);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:holiday');
    }

}
