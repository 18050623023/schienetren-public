<?php

namespace App\Tenancy;

use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class CustomDomainTenantResolver extends DomainTenantResolver
{
    public function resolveWithoutCache(...$args): Tenant
    {
        $domain = $args[0];

        if (in_array($domain, config('tenancy.central_domains', []))) {
            // Kembalikan parent behaviour: jangan identifikasi tenant
            throw new \Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException($domain);
        }

        $tenant = config('tenancy.tenant_model')::query()
            ->whereHas('domains', fn($q) => $q->where('domain', $domain))
            ->first();

        if (! $tenant) {
            abort(404, 'Tenant tidak ditemukan');
            // atau:
            // return redirect('/'); // Tidak bisa langsung, harus abort()
        }

        // Panggil resolver asli
        $tenant = parent::resolveWithoutCache($domain);

        // Cek kolom is_active
        if ($tenant && ! $tenant->is_active) {
            abort(403, 'Tenant non-aktif. Tunggu Acc dari Management Admin.');
        }

        return $tenant;
    }
}
