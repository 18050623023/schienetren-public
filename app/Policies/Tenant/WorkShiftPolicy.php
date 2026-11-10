<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\WorkShift;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class WorkShiftPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:work_shift');
    }

    public function view(authUser $authUser, WorkShift $work_shift): bool
    {
        return $this->checkOwnership($authUser, 'view:work_shift', $work_shift);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:work_shift');
    }

    public function update(authUser $authUser, WorkShift $work_shift): bool
    {
        return $this->checkOwnership($authUser, 'update:work_shift', $work_shift);
    }

    public function delete(authUser $authUser, WorkShift $work_shift): bool
    {
        return $this->checkOwnership($authUser, 'delete:work_shift', $work_shift);
    }

    public function restore(authUser $authUser, WorkShift $work_shift): bool
    {
        return $this->checkOwnership($authUser, 'restore:work_shift', $work_shift);
    }

    public function forceDelete(authUser $authUser, WorkShift $work_shift): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:work_shift', $work_shift);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:work_shift');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:work_shift');
    }

    public function replicate(authUser $authUser, WorkShift $work_shift): bool
    {
        return $this->checkOwnership($authUser, 'replicate:work_shift', $work_shift);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:work_shift');
    }

}
