<?php

namespace App\Policies;

use App\Models\User as authUser;
use App\Models\Tenant;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantPolicy
{
    use HandlesAuthorization;
    use \App\Policies\Traits\HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        return $authUser->can('viewAny:tenant');
    }

    public function view(authUser $authUser, Tenant $tenant): bool
    {
        return $this->checkOwnership($authUser, 'view:tenant', $tenant);
    }

    public function create(authUser $authUser): bool
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        if ($panel && $panel->getId() === 'portal') {
            $activeSubscription = $authUser->getActiveSubscription();
            // \Illuminate\Support\Facades\Log::info('Cek Subscription: ', ['data' => $activeSubscription]);

            if (!$activeSubscription) return false;

            $plan = $activeSubscription->plan()->first();
            if (!$plan) return false;

            // Ambil limit maksimal tenant dari fungsi di model Plan
            $maxTenants = (int) $plan->getFeatureValue(\App\Enums\TenantFeature::TENANT);
            return $authUser->tenants()->count() < $maxTenants;
            // return true;
        }
        return $authUser->can('create:tenant');
        // return true;
    }

    public function update(authUser $authUser, Tenant $tenant): bool
    {
        return $this->checkOwnership($authUser, 'update:tenant', $tenant);
    }

    public function delete(authUser $authUser, Tenant $tenant): bool
    {
        return $this->checkOwnership($authUser, 'delete:tenant', $tenant);
    }

    public function restore(authUser $authUser, Tenant $tenant): bool
    {
        return $this->checkOwnership($authUser, 'restore:tenant', $tenant);
    }

    public function forceDelete(authUser $authUser, Tenant $tenant): bool
    {
        return $this->checkOwnership($authUser, 'forceDelete:tenant', $tenant);
    }

    public function forceDeleteAny(authUser $authUser): bool
    {
        return $authUser->can('forceDeleteAny:tenant');
    }

    public function restoreAny(authUser $authUser): bool
    {
        return $authUser->can('restoreAny:tenant');
    }

    public function replicate(authUser $authUser, Tenant $tenant): bool
    {
        return $this->checkOwnership($authUser, 'replicate:tenant', $tenant);
    }

    public function reorder(authUser $authUser): bool
    {
        return $authUser->can('reorder:tenant');
    }
}
