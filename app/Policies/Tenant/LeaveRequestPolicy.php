<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\User as authUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class LeaveRequestPolicy
{
    use HandlesAuthorization, HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::LEAVE)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'viewAny:leave_request');
    }

    public function view(authUser $authUser, LeaveRequest $leave_request): bool
    {

        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }

        return $authUser->can('view:leave_request')
        && $leave_request->employee->user_id === $authUser->id;
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::LEAVE)) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:leave_request');
    }

    public function update(authUser $authUser, LeaveRequest $leave_request): bool
    {

        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }

        return $authUser->can('update:leave_request')
        && $leave_request->employee->user_id === $authUser->id;
    }

    public function delete(authUser $authUser, LeaveRequest $leave_request): bool
    {

        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }

        return $authUser->can('delete:leave_request')
        && $leave_request->employee->user_id === $authUser->id;
    }

    public function restore(authUser $authUser, LeaveRequest $leave_request): bool
    {

        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }

        return $authUser->can('restore:leave_request')
        && $leave_request->employee->user_id === $authUser->id;
    }

    public function forceDelete(authUser $authUser, LeaveRequest $leave_request): bool
    {

        if ($authUser->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            return true;
        }

        return $authUser->can('forceDelete:leave_request')
        && $leave_request->employee->user_id === $authUser->id;
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:leave_request');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:leave_request');
    }

    public function replicate(authUser $authUser, LeaveRequest $leave_request): bool
    {
        return $this->checkOwnership($authUser, 'replicate:leave_request', $leave_request);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:leave_request');
    }


    // JANGAN DI HAPUS
    public function approve(authUser $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->status === 'pending'
            && $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']));
    }
}
