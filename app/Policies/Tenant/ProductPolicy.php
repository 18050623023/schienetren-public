<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Product;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class ProductPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:product');
    }

    public function view(authUser $authUser, Product $product): bool
    {
        return $this->checkOwnership($authUser, 'view:product', $product);
    }

    public function create(authUser $authUser): bool
    {
        if (!Product::checkProductLimit()->passed) {
            return false;
        }

        return $this->checkOwnership($authUser, 'create:product');
    }

    public function update(authUser $authUser, Product $product): bool
    {
        return $this->checkOwnership($authUser, 'update:product', $product);
    }

    public function delete(authUser $authUser, Product $product): bool
    {
        return $this->checkOwnership($authUser, 'delete:product', $product);
    }

    public function restore(authUser $authUser, Product $product): bool
    {
        return $this->checkOwnership($authUser, 'restore:product', $product);
    }

    public function forceDelete(authUser $authUser, Product $product): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:product', $product);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:product');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:product');
    }

    public function replicate(authUser $authUser, Product $product): bool
    {
        return $this->checkOwnership($authUser, 'replicate:product', $product);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:product');
    }

}
