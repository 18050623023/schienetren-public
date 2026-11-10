<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Repair;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class RepairPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:repair');
    }

    public function view(authUser $authUser, Repair $repair): bool
    {
        return $this->checkOwnership($authUser, 'view:repair', $repair);
    }

    public function create(authUser $authUser): bool
    {
        if (!Repair::checkMonthlyRepairLimit()->passed) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:repair');
    }

    public function update(authUser $authUser, Repair $repair): bool
    {
        return $this->checkOwnership($authUser, 'update:repair', $repair);
    }

    public function delete(authUser $authUser, Repair $repair): bool
    {
        return $this->checkOwnership($authUser, 'delete:repair', $repair);
    }

    public function restore(authUser $authUser, Repair $repair): bool
    {
        return $this->checkOwnership($authUser, 'restore:repair', $repair);
    }

    public function forceDelete(authUser $authUser, Repair $repair): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:repair', $repair);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:repair');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:repair');
    }

    public function replicate(authUser $authUser, Repair $repair): bool
    {
        return $this->checkOwnership($authUser, 'replicate:repair', $repair);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:repair');
    }

}
