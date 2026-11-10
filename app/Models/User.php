<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CentralConnection, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'email_verified_at',
        'role',
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

    public function getActiveSubscription()
    {
        // Mengambil subscription user yang statusnya aktif, 
        // dan belum kedaluwarsa (opsional, tergantung struktur tabel Anda)
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>=', now()) // Jika ada kolom expired
            ->latest()
            ->first();
    }

    public function getScheduledSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'scheduled')
            ->orderBy('starts_at', 'asc')
            ->first();
    }


    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasVerifiedEmail() && $this->role === 'super_admin';
        }

        if ($panel->getId() === 'portal') {
            return true; // Anyone can access their own portal
        }

        return true;
    }
}
