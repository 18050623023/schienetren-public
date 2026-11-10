<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SetupTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Tenant $tenant)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Aktifkan tenant
        tenancy()->initialize($this->tenant);

        // Jalankan migrasi
        Artisan::call('tenants:migrate', [
            '--tenants' => [$this->tenant->id],
        ]);

        // Nonaktifkan tenant
        tenancy()->end();

        // Sinkronisasi fitur subscription (harus dipanggil setelah tenant di-end, karena fungsi ini menggunakan run())
        if ($this->tenant->subscription_id) {
            $subscription = \App\Models\Subscription::find($this->tenant->subscription_id);
            if ($subscription) {
                \App\Services\TenantManagerService::syncSubscriptionFeatures($subscription);
            }
        }
    }
}
