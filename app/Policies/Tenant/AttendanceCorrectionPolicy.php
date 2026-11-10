<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\AttendanceCorrection;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class AttendanceCorrectionPolicy
{
    use HandlesAuthorization, HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:attendance_correction');
    }

    public function view(authUser $authUser, AttendanceCorrection $attendance_correction): bool
    {
        return $this->checkOwnership($authUser, 'view:attendance_correction', $attendance_correction);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:attendance_correction');
    }

    public function update(authUser $authUser, AttendanceCorrection $attendance_correction): bool
    {
        return $this->checkOwnership($authUser, 'update:attendance_correction', $attendance_correction);
    }

    public function delete(authUser $authUser, AttendanceCorrection $attendance_correction): bool
    {
        return $this->checkOwnership($authUser, 'delete:attendance_correction', $attendance_correction);
    }

    public function deleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'deleteAny:attendance_correction');
    }

    public function forceDelete(authUser $authUser, AttendanceCorrection $attendance_correction): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:attendance_correction', $attendance_correction);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $authUser->can('forceDeleteAny:attendance_correction');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $authUser->can('restoreAny:attendance_correction');
    }

    public function replicate(authUser $authUser, AttendanceCorrection $attendance_correction): bool
    {
        return $authUser->can('replicate:attendance_correction');
    }

    public function reorder(authUser $authUser): bool
    {
        return $authUser->can('reorder:attendance_correction');
    }

}
