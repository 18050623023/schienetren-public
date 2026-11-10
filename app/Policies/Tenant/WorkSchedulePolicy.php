<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\WorkSchedule;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class WorkSchedulePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:work_schedule');
    }

    public function view(authUser $authUser, WorkSchedule $work_schedule): bool
    {
        return $this->checkOwnership($authUser, 'view:work_schedule', $work_schedule);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:work_schedule');
    }

    public function update(authUser $authUser, WorkSchedule $work_schedule): bool
    {
        return $this->checkOwnership($authUser, 'update:work_schedule', $work_schedule);
    }

    public function delete(authUser $authUser, WorkSchedule $work_schedule): bool
    {
        return $this->checkOwnership($authUser, 'delete:work_schedule', $work_schedule);
    }

    public function restore(authUser $authUser, WorkSchedule $work_schedule): bool
    {
        return $this->checkOwnership($authUser, 'restore:work_schedule', $work_schedule);
    }

    public function forceDelete(authUser $authUser, WorkSchedule $work_schedule): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:work_schedule', $work_schedule);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:work_schedule');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:work_schedule');
    }

    public function replicate(authUser $authUser, WorkSchedule $work_schedule): bool
    {
        return $this->checkOwnership($authUser, 'replicate:work_schedule', $work_schedule);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:work_schedule');
    }

}
