<?php

namespace Database\Seeders;

use App\Enums\TenantFeature;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\TenantManagerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data master features
        $plans = Plan::all();
        $features = Feature::all()->keyBy('code');

        $defaultLimits = [
            'tenant' => '5',
            'max_user' => '20',
            'max_branch' => '5',
            'max_product' => '1000',
            'max_monthly_repair' => '500',
        ];

        // 2. Kaitkan SEMUA fitur ke setiap Plan untuk keperluan pengujian penuh
        foreach ($plans as $plan) {
            $sortOrder = 1;
            foreach (TenantFeature::cases() as $case) {
                $feature = $features->get($case->value);
                if (!$feature) {
                    continue;
                }

                $value = $feature->type === 'limit'
                    ? ($defaultLimits[$case->value] ?? '10')
                    : '1';

                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => $feature->id,
                    ],
                    [
                        'value' => (string) $value,
                        'sort_order' => $sortOrder++,
                    ]
                );
            }
        }

        $this->command->info("Semua " . count(TenantFeature::cases()) . " fitur berhasil dikaitkan ke " . $plans->count() . " Paket Langganan (Plan).");

        // 3. Sinkronkan fitur ke database tenant menggunakan TenantManagerService
        $subscriptions = Subscription::all();
        foreach ($subscriptions as $subscription) {
            TenantManagerService::syncSubscriptionFeatures($subscription);
        }

        $this->command->info("TenantManagerService::syncSubscriptionFeatures() berhasil disinkronkan ke tenant.");

        // 4. Pastikan toggle di database tenant 'schienetren' aktif (is_enabled = 1)
        $tenant = Tenant::find('schienetren');
        if ($tenant) {
            $tenant->run(function () {
                $featureLimits = DB::table('tenant_feature_limits')->get();
                foreach ($featureLimits as $limit) {
                    DB::table('tenant_feature_toggles')->updateOrInsert(
                        ['feature_code' => $limit->feature_code],
                        [
                            'is_enabled' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
            $this->command->info("Semua toggle di tenant 'schienetren' berhasil diaktifkan (is_enabled = 1).");
        }
    }
}
