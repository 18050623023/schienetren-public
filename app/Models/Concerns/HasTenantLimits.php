<?php

namespace App\Models\Concerns;

use App\Enums\TenantFeature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @method static Builder query()
 */
trait HasTenantLimits
{
    /**
     * Mengecek apakah penambahan/pengaktifan data melebihi batas paket langganan.
     *
     * @param TenantFeature $feature Fitur yang akan dicek (misal: MAX_BRANCH, MAX_USER).
     * @param string $entityName Nama entitas untuk pesan error (misal: 'cabang aktif', 'pengguna').
     * @param string|null $booleanColumn Kolom boolean untuk filter hitungan (misal: 'is_active'). Null jika menghitung seluruh baris.
     * @param self|null $record Model yang sedang diubah (untuk tidak dihitung ganda jika status sebelumnya sudah masuk hitungan).
     * @return \App\DTOs\FeatureCheckResult Hasil pengecekan batas.
     */
    public static function checkFeatureLimit(
        TenantFeature $feature,
        string $entityName,
        ?string $booleanColumn = null,
        ?Model $record = null
    ): \App\DTOs\FeatureCheckResult {
        $tenant = tenant();
        if (!$tenant) {
            return \App\DTOs\FeatureCheckResult::passed(); // Bypass jika tidak ada konteks tenant
        }

        // Cek status toggle on/off dari fitur
        $toggle = \App\Models\Tenant\FeatureToggle::where('feature_code', $feature->value)->first();
        if ($toggle && !$toggle->is_enabled) {
            return \App\DTOs\FeatureCheckResult::disabled("Fitur {$entityName} sedang dinonaktifkan.");
        }

        $maxLimit = $tenant->getFeatureValue($feature);
        if ($maxLimit === null || $maxLimit === false || $maxLimit < 0) {
            return \App\DTOs\FeatureCheckResult::passed(); // Unlimited atau fitur tidak ditemukan
        }

        // Mulai menghitung data
        $query = static::query();
        if ($booleanColumn) {
            $query->where($booleanColumn, true);
        }
        $currentCount = $query->count();

        // Mengurangi hitungan jika data yang sedang di-edit sudah dihitung sebelumnya
        if ($record && ! empty($record->exists)) {
            if ($booleanColumn) {
                if ($record->{$booleanColumn}) {
                    $currentCount--; 
                }
            } else {
                $currentCount--; 
            }
        }

        if ($currentCount >= $maxLimit) {
            return \App\DTOs\FeatureCheckResult::limitExceeded("Batas maksimal {$entityName} ({$maxLimit}) telah tercapai sesuai paket langganan Anda.");
        }

        return \App\DTOs\FeatureCheckResult::passed();
    }
}
