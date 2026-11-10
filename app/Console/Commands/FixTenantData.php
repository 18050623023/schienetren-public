<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class FixTenantData extends Command
{
    protected $signature = 'tenant:fix-data';

    protected $description = 'Menambahkan default fitur ke semua tenant lama';

    public function handle()
    {
        $tenants = Tenant::first();

        // foreach ($tenants as $tenant) {
            $data = $tenant->data ?? [];

            // Jika belum ada fitur, tambahkan
            // $data['features'] = array_merge([
            //     'branch' => false,
            //     'client' => false,
            // ], $data['features'] ?? []);

            // $tenant->data = $data;
            // $return = $tenant->save();
            dd($tenants->features);
            // dd($tenants);
            // $this->info("Updated tenant: {$tenant->id}");
        // }

        // $this->info('Selesai!');
    }
}
