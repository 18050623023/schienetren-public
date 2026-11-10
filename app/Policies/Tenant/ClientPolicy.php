<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User as authUser;
use App\Models\Tenant\Client;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Traits\HasRecordRules;

class ClientPolicy
{
    use HandlesAuthorization, HasRecordRules;
    
    public function viewAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'viewAny:client');
    }

    public function view(authUser $authUser, Client $client): bool
    {
        return $this->checkOwnership($authUser, 'view:client', $client);
    }

    public function create(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'create:client');
    }

    public function update(authUser $authUser, Client $client): bool
    {
        return $this->checkOwnership($authUser, 'update:client', $client);
    }

    public function delete(authUser $authUser, Client $client): bool
    {
        return $this->checkOwnership($authUser, 'delete:client', $client);
    }

    public function restore(authUser $authUser, Client $client): bool
    {
        return $this->checkOwnership($authUser, 'restore:client', $client);
    }

    public function forceDelete(authUser $authUser, Client $client): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:client', $client);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'forceDeleteAny:client');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'restoreAny:client');
    }

    public function replicate(authUser $authUser, Client $client): bool
    {
        return $this->checkOwnership($authUser, 'replicate:client', $client);
    }

    public function reorder(authUser $authUser): bool
    {
        return $this->checkOwnership($authUser, 'reorder:client');
    }

}
