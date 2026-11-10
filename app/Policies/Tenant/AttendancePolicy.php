<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Attendance;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class AttendancePolicy
{
    use HandlesAuthorization, HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:attendance');
    }

    public function view(authUser $authUser, Attendance $attendance): bool
    {
        return $this->checkOwnership($authUser, 'view:attendance', $attendance);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:attendance');
    }

    public function update(authUser $authUser, Attendance $attendance): bool
    {
        return $this->checkOwnership($authUser, 'update:attendance', $attendance);
    }

    public function delete(authUser $authUser, Attendance $attendance): bool
    {
        return $this->checkOwnership($authUser, 'delete:attendance', $attendance);
    }

    public function deleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'deleteAny:attendance');
    }

    public function forceDelete(authUser $authUser, Attendance $attendance): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:attendance', $attendance);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:attendance');
    }

    public function restore(authUser $authUser, Attendance $attendance): bool
    {
        return $this->checkOwnership($authUser, 'restore:attendance', $attendance);
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:attendance');
    }

    public function replicate(authUser $authUser, Attendance $attendance): bool
    {
        return $this->checkOwnership($authUser, 'replicate:attendance', $attendance);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:attendance');
    }

    // JANGAN DI HAPUS
    public function approve(authUser $user, Attendance $attendance): bool
    {
        return $attendance->requires_schedule_review
            && $attendance->review_status === 'pending'
            && $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']));
    }

}
