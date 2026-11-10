<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Promo;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class PromoPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:promo');
    }

    public function view(authUser $authUser, Promo $promo): bool
    {
        return $this->checkOwnership($authUser, 'view:promo', $promo);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:promo');
    }

    public function update(authUser $authUser, Promo $promo): bool
    {
        return $this->checkOwnership($authUser, 'update:promo', $promo);
    }

    public function delete(authUser $authUser, Promo $promo): bool
    {
        return $this->checkOwnership($authUser, 'delete:promo', $promo);
    }

    public function restore(authUser $authUser, Promo $promo): bool
    {
        return $this->checkOwnership($authUser, 'restore:promo', $promo);
    }

    public function forceDelete(authUser $authUser, Promo $promo): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:promo', $promo);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:promo');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:promo');
    }

    public function replicate(authUser $authUser, Promo $promo): bool
    {
        return $this->checkOwnership($authUser, 'replicate:promo', $promo);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:promo');
    }

}
