<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantManagerService
{
    /**
     * Membuat tenant baru dan menjalankan job setup
     * 
     * @param array $data
     * @return Tenant
     * @throws \Exception
     */
    public static function createTenant(array $data): Tenant
    {
        // 1. Generate ID jika kosong
        if (empty($data['id'])) {
            $data['id'] = \Illuminate\Support\Str::slug($data['business_name']);
        }

        // 2. Cek apakah database sudah terdaftar
        if (function_exists('checkTenantDatabase') && checkTenantDatabase($data['id'])) {
            throw new \Exception('Database Sudah terdaftar');
        }

        // 3. Set user_id jika bukan super admin
        if (!isset($data['user_id'])) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && method_exists($user, 'hasRole') && !$user->hasRole('super_admin')) {
                $data['user_id'] = $user->id;
            }
        }

        // 4. Buat record Tenant
        $tenant = Tenant::create($data);

        // 5. Dispatch job setup tenant
        self::setupNewTenant($tenant);

        return $tenant;
    }

    /**
     * Setup tenant baru di background (Migrasi dll)
     */
    public static function setupNewTenant(Tenant $tenant)
    {
        \App\Jobs\SetupTenant::dispatch($tenant);
    }
    /**
     * Sinkronkan fitur dari Subscription dan Addon ke tabel tenant_feature_limits
     * 
     * @param Subscription $subscription
     */
    public static function syncSubscriptionFeatures(Subscription $subscription)
    {
        // 1. Ambil semua Tenant yang terikat pada subscription ini
        $tenants = Tenant::where('subscription_id', $subscription->id)->get();

        if ($tenants->isEmpty()) {
            return;
        }

        // 2. Kumpulkan Fitur Paket Dasar
        // Base features are shared across all tenants of this subscription
        $planFeatures = $subscription->plan->planFeatures()->with('feature')->get();
        
        $baseFeatures = [];
        foreach ($planFeatures as $pf) {
            if ($pf->feature) {
                $baseFeatures[$pf->feature->code] = [
                    'type' => $pf->feature->type,
                    'value' => $pf->value,
                ];
            }
        }
        // 2b. Ensure all enum features are present with default values
        foreach (\App\Enums\TenantFeature::cases() as $enumCase) {
            $code = $enumCase->value;
            if (!isset($baseFeatures[$code])) {
                $baseFeatures[$code] = [
                    'type' => $enumCase->getType(),
                    'value' => $enumCase->getDefaultValue(),
                ];
            }
        }

        // 3. Looping setiap tenant untuk memasukkan fitur dasar + addon spesifik tenant
        foreach ($tenants as $tenant) {
            $mergedFeatures = $baseFeatures; // clone base features for this tenant

            // Kumpulkan Fitur Addon khusus untuk tenant ini yang sedang aktif
            $addons = $subscription->subscriptionAddons()
                ->where('tenant_id', $tenant->id)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->with('addon.addonFeatures.feature')
                ->get();

            foreach ($addons as $addonItem) {
                if (!$addonItem->addon) continue;
                
                $qty = $addonItem->qty;
                
                foreach ($addonItem->addon->addonFeatures as $addonFeature) {
                    if (!$addonFeature->feature) continue;
                    
                    $code = $addonFeature->feature->code;
                    $type = $addonFeature->feature->type;
                    $value = $addonFeature->value;

                    if (!isset($mergedFeatures[$code])) {
                        $mergedFeatures[$code] = [
                            'type' => $type,
                            'value' => $type === 'limit' ? 0 : false,
                        ];
                    }

                    // Gabungkan
                    if ($type === 'limit') {
                        // Jumlahkan limit (Value dari addon * kuantitas)
                        $numericValue = (float) $value;
                        $mergedFeatures[$code]['value'] = (float) $mergedFeatures[$code]['value'] + ($numericValue * $qty);
                    } elseif ($type === 'boolean') {
                        // Jika true, maka OR true
                        $boolValue = in_array($value, [true, 'true', '1', 1], true);
                        if ($boolValue) {
                            $mergedFeatures[$code]['value'] = true;
                        }
                    }
                }
            }

            // 4. Simpan ke DB Tenant
            $tenant->run(function () use ($mergedFeatures) {
                DB::transaction(function () use ($mergedFeatures) {
                    // Hapus semua data lama agar bersih
                    DB::table('tenant_feature_limits')->delete();

                    $insertData = [];
                    $now = now();

                    foreach ($mergedFeatures as $code => $data) {
                        // Normalize boolean to string/int for DB
                        $finalValue = $data['value'];
                        if ($data['type'] === 'boolean') {
                            $finalValue = $finalValue ? '1' : '0';
                        }

                        $insertData[] = [
                            'feature_code' => $code,
                            'feature_type' => $data['type'],
                            'max_value' => (string) $finalValue,
                            'is_expired' => false,
                            'synced_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if (!empty($insertData)) {
                        DB::table('tenant_feature_limits')->insert($insertData);
                    }
                });
            });
        }
    }
}
