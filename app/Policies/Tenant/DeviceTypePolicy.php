<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\DeviceType;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class DeviceTypePolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:device_type');
    }

    public function view(authUser $authUser, DeviceType $device_type): bool
    {
        return $this->checkOwnership($authUser, 'view:device_type', $device_type);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:device_type');
    }

    public function update(authUser $authUser, DeviceType $device_type): bool
    {
        return $this->checkOwnership($authUser, 'update:device_type', $device_type);
    }

    public function delete(authUser $authUser, DeviceType $device_type): bool
    {
        return $this->checkOwnership($authUser, 'delete:device_type', $device_type);
    }

    public function restore(authUser $authUser, DeviceType $device_type): bool
    {
        return $this->checkOwnership($authUser, 'restore:device_type', $device_type);
    }

    public function forceDelete(authUser $authUser, DeviceType $device_type): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:device_type', $device_type);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:device_type');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:device_type');
    }

    public function replicate(authUser $authUser, DeviceType $device_type): bool
    {
        return $this->checkOwnership($authUser, 'replicate:device_type', $device_type);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:device_type');
    }

}
