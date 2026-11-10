<?php

namespace App\Policies\Traits;

use App\Contracts\Ownable;

trait HasRecordRules
{
    /**
     * Memeriksa otoritas user berdasarkan Spatie dan Aturan Kepemilikan.
     */
    protected function checkOwnership($user, $permission, $model = null): bool
    {
        // 1. Pengecekan Spatie (Apakah dia boleh mengakses fitur ini?)
        if (!$user->can($permission)) {
            return false;
        }

        // 2. Bypass untuk Super Admin & Owner
        $bypassRoles = tenant() 
            ? settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']) 
            : settingCentral('central_bypass_roles', ['super_admin', 'owner', 'admin']);

        if ($user->hasRole($bypassRoles)) {
            return true;
        }

        // 3. Pengecekan Record Rule (Kepemilikan Baris Data)
        // Jika parameter model dikirimkan, dan model tersebut mengimplementasi Ownable
        if ($model instanceof Ownable) {
            return $user->id === $model->getOwnerId();
        }

        // Jika model TIDAK Ownable (misal tabel Brand, Category), 
        // maka lepaskan! (Karena data master boleh dilihat semua yang punya izin)
        return true;
    }
}
