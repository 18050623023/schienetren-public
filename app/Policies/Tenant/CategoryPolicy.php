<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Category;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class CategoryPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:category');
    }

    public function view(authUser $authUser, Category $category): bool
    {
        return $this->checkOwnership($authUser, 'view:category', $category);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:category');
    }

    public function update(authUser $authUser, Category $category): bool
    {
        return $this->checkOwnership($authUser, 'update:category', $category);
    }

    public function delete(authUser $authUser, Category $category): bool
    {
        return $this->checkOwnership($authUser, 'delete:category', $category);
    }

    public function restore(authUser $authUser, Category $category): bool
    {
        return $this->checkOwnership($authUser, 'restore:category', $category);
    }

    public function forceDelete(authUser $authUser, Category $category): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:category', $category);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:category');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:category');
    }

    public function replicate(authUser $authUser, Category $category): bool
    {
        return $this->checkOwnership($authUser, 'replicate:category', $category);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:category');
    }

}
