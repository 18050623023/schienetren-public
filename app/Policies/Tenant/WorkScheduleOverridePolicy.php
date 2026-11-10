<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\WorkScheduleOverride;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class WorkScheduleOverridePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:work_schedule_override');
    }

    public function view(authUser $authUser, WorkScheduleOverride $work_schedule_override): bool
    {
        return $this->checkOwnership($authUser, 'view:work_schedule_override', $work_schedule_override);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:work_schedule_override');
    }

    public function update(authUser $authUser, WorkScheduleOverride $work_schedule_override): bool
    {
        return $this->checkOwnership($authUser, 'update:work_schedule_override', $work_schedule_override);
    }

    public function delete(authUser $authUser, WorkScheduleOverride $work_schedule_override): bool
    {
        return $this->checkOwnership($authUser, 'delete:work_schedule_override', $work_schedule_override);
    }

    public function restore(authUser $authUser, WorkScheduleOverride $work_schedule_override): bool
    {
        return $this->checkOwnership($authUser, 'restore:work_schedule_override', $work_schedule_override);
    }

    public function forceDelete(authUser $authUser, WorkScheduleOverride $work_schedule_override): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:work_schedule_override', $work_schedule_override);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:work_schedule_override');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:work_schedule_override');
    }

    public function replicate(authUser $authUser, WorkScheduleOverride $work_schedule_override): bool
    {
        return $this->checkOwnership($authUser, 'replicate:work_schedule_override', $work_schedule_override);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:work_schedule_override');
    }

}
