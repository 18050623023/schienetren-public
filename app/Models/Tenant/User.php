<?php

namespace App\Models\Tenant;

use App\Policies\Tenant\UserPolicy;
use App\Models\Concerns\HasTenantLimits;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TenantConnection, HasTenantLimits;

    protected $guard_name = 'tenant';

    // public $connection = 'tenant';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // public function getRoleClass()
    // {
    //     return \App\Models\Tenant\Role::class;
    // }

    // public function getPermissionClass()
    // {
    //     return \App\Models\Tenant\Permission::class;
    // }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'tenant') {
            return $this->hasVerifiedEmail();
        }

        return true;
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function employees()
    {
        return $this->hasOne(Employee::class);
    }

    public function isInBranch($branchId): bool
    {
        if (! $branchId) {
            return false;
        }

        return $this->employees()
            ->whereHas('assignments', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', today());
                    });
            })->exists();
    }


    public function scopeInBranch($query, $branchId)
    {
        return $query->whereHas('employees.assignments', function ($query) use ($branchId) {
            $query
                ->where('branch_id', $branchId)
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', today());
                });
        });
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($user) {
            $check = static::checkUserLimit();
            if (!$check->passed) {
                throw new \Exception($check->message);
            }
        });
    }

    public static function checkUserLimit(?self $record = null): \App\DTOs\FeatureCheckResult
    {
        return self::checkFeatureLimit(
            \App\Enums\TenantFeature::MAX_USER,
            'pengguna / staf toko',
            null,
            $record
        );
    }
}
