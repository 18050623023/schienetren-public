<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Unit;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class UnitPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:unit');
    }

    public function view(authUser $authUser, Unit $unit): bool
    {
        return $this->checkOwnership($authUser, 'view:unit', $unit);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:unit');
    }

    public function update(authUser $authUser, Unit $unit): bool
    {
        return $this->checkOwnership($authUser, 'update:unit', $unit);
    }

    public function delete(authUser $authUser, Unit $unit): bool
    {
        return $this->checkOwnership($authUser, 'delete:unit', $unit);
    }

    public function restore(authUser $authUser, Unit $unit): bool
    {
        return $this->checkOwnership($authUser, 'restore:unit', $unit);
    }

    public function forceDelete(authUser $authUser, Unit $unit): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:unit', $unit);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:unit');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:unit');
    }

    public function replicate(authUser $authUser, Unit $unit): bool
    {
        return $this->checkOwnership($authUser, 'replicate:unit', $unit);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:unit');
    }

}
