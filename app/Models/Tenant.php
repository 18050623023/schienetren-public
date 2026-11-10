<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Models\Tenant as ModelsTenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

use App\Contracts\Ownable;

class Tenant extends ModelsTenant implements TenantWithDatabase, HasName, Ownable
{
    // use SoftDeletes;
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'business_name',
        'is_active',
        'user_id',
        'subscription_id',
        'version'
    ];

    public function getOwnerId()
    {
        return $this->user_id;
    }

    protected $casts = [
        'is_active' => 'boolean',
        'data' => 'array',
        'version' => 'integer',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'business_name',
            'user_id',
            'subscription_id',
            'data',
            'version',
            'is_active',
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($tenant) {
            if (Auth::check() && !Auth::user()->hasRole('super_admin')) {
                $tenant->user_id = Auth::id();
            }
        });
    }

    // Relasi Domain custom ke Tenant
    public function domain(): HasOne
    {
        return $this->hasOne(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function subscriptionAddons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubscriptionAddon::class);
    }

    public function getFilamentName(): string
    {
        return $this->business_name;
    }

    protected static array $featureCache = [];

    public static function flushFeatureCache(): void
    {
        self::$featureCache = [];
    }

    /**
     * Ambil nilai dari fitur (Bisa berupa boolean true/false atau integer kuota/limit)
     */
    public function getFeatureValue(\App\Enums\TenantFeature $feature)
    {
        $cacheKey = $this->id . '_' . $feature->value;

        if (array_key_exists($cacheKey, self::$featureCache)) {
            return self::$featureCache[$cacheKey];
        }

        self::$featureCache[$cacheKey] = $this->run(function () use ($feature) {
            $limit = \Illuminate\Support\Facades\DB::table('tenant_feature_limits')
                ->where('feature_code', $feature->value)
                ->where('is_expired', false)
                ->first();

            if (!$limit) {
                return false;
            }

            if ($limit->feature_type === 'boolean') {
                return ($limit->max_value == 1 || $limit->max_value === 'true');
            }

            if ($limit->feature_type === 'limit') {
                if ($limit->max_value === null || $limit->max_value === '' || $limit->max_value === 'unlimited' || $limit->max_value === '-1') {
                    return null; // null = Unlimited
                }
                return (int) $limit->max_value;
            }

            return $limit->max_value;
        });

        return self::$featureCache[$cacheKey];
    }

    /**
     * Cek apakah tenant memiliki hak akses ke fitur (Return Boolean murni)
     * Tetap dipertahankan agar tidak merusak kode yang sudah ada.
     */
    public function hasFeatureCode(\App\Enums\TenantFeature $feature): bool
    {
        // 1. Cek dari jatah paket langganan (tenant_feature_limits)
        $hasSubscription = (bool) $this->getFeatureValue($feature);
        if (!$hasSubscription) {
            return false;
        }

        // 2. Cek status toggle on/off dari pengaturan tenant (FeatureToggle)
        // Kita bungkus dengan $this->run() agar aman saat diakses dari Central Panel (Admin Utama)
        $isToggleEnabled = $this->run(function () use ($feature) {
            $toggle = \App\Models\Tenant\FeatureToggle::where('feature_code', $feature->value)->first();
            return $toggle ? (bool) $toggle->is_enabled : true;
        });

        if (!$isToggleEnabled) {
            return false;
        }

        return true;
    }

    /**
     * Menyimpan data dengan Optimistic Locking.
     * Menggunakan query builder agar kita tahu jumlah baris yang berhasil diupdate.
     */
    public function saveWithLock()
    {
        $dirty = $this->getDirty();
        
        if (count($dirty) > 0) {
            $expectedVersion = $this->version;

            // Tambahkan increment version ke data yang akan diupdate
            $updateData = array_merge($dirty, [
                'version' => $expectedVersion + 1
            ]);

            // Eksekusi UPDATE dengan klausa WHERE version = X
            $affected = static::where($this->getKeyName(), $this->getKey())
                ->where('version', $expectedVersion)
                ->update($updateData);

            if ($affected === 0) {
                throw new \Exception("Data tenant sedang diubah oleh proses lain (Race Condition). Silakan coba lagi.");
            }

            // Sesuaikan state model di memory agar sinkron
            $this->version = $expectedVersion + 1;
            $this->syncChanges();
            
            return true;
        }

        return false;
    }

    /**
     * Ambil atau buat otomatis Client default (Pelanggan Umum).
     * Digunakan ketika fitur CLIENT dinonaktifkan.
     */
    public function getDefaultClient()
    {
        return $this->run(function () {
            $defaultName = settingTenant('default_client_name', 'Pelanggan Umum');
            
            // Gunakan firstOrCreate untuk mengambil atau membuat otomatis jika belum ada
            return \App\Models\Tenant\Client::firstOrCreate(
                ['name' => $defaultName],
                [
                    'phone' => '-',
                    'whatsapp' => '-',
                    'address' => 'Pelanggan Walk-in',
                ]
            );
        });
    }
}
