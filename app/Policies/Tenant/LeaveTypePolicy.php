<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\User as authUser;
use App\Models\Tenant\LeaveType;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class LeaveTypePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::LEAVE)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:leave_type');
    }

    public function view(authUser $authUser, LeaveType $leave_type): bool
    {
        return $this->checkOwnership($authUser, 'view:leave_type', $leave_type);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::LEAVE)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:leave_type');
    }

    public function update(authUser $authUser, LeaveType $leave_type): bool
    {
        return $this->checkOwnership($authUser, 'update:leave_type', $leave_type);
    }

    public function delete(authUser $authUser, LeaveType $leave_type): bool
    {
        return $this->checkOwnership($authUser, 'delete:leave_type', $leave_type);
    }

    public function restore(authUser $authUser, LeaveType $leave_type): bool
    {
        return $this->checkOwnership($authUser, 'restore:leave_type', $leave_type);
    }

    public function forceDelete(authUser $authUser, LeaveType $leave_type): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:leave_type', $leave_type);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:leave_type');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:leave_type');
    }

    public function replicate(authUser $authUser, LeaveType $leave_type): bool
    {
        return $this->checkOwnership($authUser, 'replicate:leave_type', $leave_type);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:leave_type');
    }

}
