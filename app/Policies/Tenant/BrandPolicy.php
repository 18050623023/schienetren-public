<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Brand;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class BrandPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:brand');
    }

    public function view(authUser $authUser, Brand $brand): bool
    {
        return $this->checkOwnership($authUser, 'view:brand', $brand);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:brand');
    }

    public function update(authUser $authUser, Brand $brand): bool
    {
        return $this->checkOwnership($authUser, 'update:brand', $brand);
    }

    public function delete(authUser $authUser, Brand $brand): bool
    {
        return $this->checkOwnership($authUser, 'delete:brand', $brand);
    }

    public function restore(authUser $authUser, Brand $brand): bool
    {
        return $this->checkOwnership($authUser, 'restore:brand', $brand);
    }

    public function forceDelete(authUser $authUser, Brand $brand): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:brand', $brand);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:brand');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:brand');
    }

    public function replicate(authUser $authUser, Brand $brand): bool
    {
        return $this->checkOwnership($authUser, 'replicate:brand', $brand);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:brand');
    }

}
