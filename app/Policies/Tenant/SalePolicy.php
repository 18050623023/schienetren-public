<?php

namespace App\Policies\Tenant;

use App\Enums\TenantFeature;
use App\Models\Tenant\Sale;
use App\Models\Tenant\User as authUser;
use App\Policies\Traits\HasRecordRules;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization, HasRecordRules;

    public function viewAny(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::POS)) {
            return false;
        }

        $bypassRoles = settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']);
        if ($authUser->hasRole($bypassRoles) || $authUser->hasRole(['cashier', 'customer_service', 'cs', 'manager']) || $authUser->can('access_pos')) {
            return true;
        }

        return $this->checkOwnership($authUser, 'viewAny:sale');
    }

    public function view(authUser $authUser, Sale $sale): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::POS)) {
            return false;
        }

        $bypassRoles = settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']);
        if ($authUser->hasRole($bypassRoles) || $authUser->hasRole(['cashier', 'customer_service', 'cs', 'manager']) || $authUser->can('access_pos')) {
            return true;
        }

        return $this->checkOwnership($authUser, 'view:sale', $sale);
    }

    public function create(authUser $authUser): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::POS)) {
            return false;
        }

        $bypassRoles = settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']);
        if ($authUser->hasRole($bypassRoles) || $authUser->hasRole(['cashier', 'customer_service', 'cs', 'manager']) || $authUser->can('access_pos')) {
            return true;
        }

        return $this->checkOwnership($authUser, 'create:sale');
    }

    public function update(authUser $authUser, Sale $sale): bool
    {
        return false;
    }

    public function delete(authUser $authUser, Sale $sale): bool
    {
        return false;
    }

    public function restore(authUser $authUser, Sale $sale): bool
    {
        return false;
    }

    public function forceDelete(authUser $authUser, Sale $sale): bool
    {
        return false;
    }
}
