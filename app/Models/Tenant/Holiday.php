<?php

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{


    // protected $fillable = [
    //     'branch_id',
    //     'name',
    //     'type',
    //     'holiday_template_id',
    //     'date',
    //     'is_active',
    // ];

    // public function branch()
    // {
    //     return $this->belongsTo(Branch::class);
    // }

    // public function holidayTemplate()
    // {
    //     return $this->belongsTo(HolidayTemplate::class);
    // }


    protected $fillable = [
        'name',
        'scope',
        'recurrence_type',
        'date',
        'month',
        'day',
        'is_paid',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function ($holiday) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(\App\Enums\TenantFeature::HOLIDAY)) {
                throw new \Exception('Fitur Hari Libur Nasional tidak aktif pada langganan Anda.');
            }
        });
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class,'holiday_branch');
    }

    public function isOneTime(): bool
    {
        return $this->recurrence_type === 'one_time';
    }

    public function isYearly(): bool
    {
        return $this->recurrence_type === 'yearly';
    }

    public function isMonthly(): bool
    {
        return $this->recurrence_type === 'monthly';
    }

    public function occursOn(Carbon $date): bool
    {
        return match ($this->recurrence_type) {
            'one_time' => $this->date?->isSameDay($date),
            'yearly' => $this->month == $date->month && $this->day == $date->day,
            'monthly' => $this->day == $date->day,
            default => false,
        };
    }

    public function getDisplayDateAttribute(): string
    {
        return match ($this->recurrence_type) {
            'one_time' => Carbon::parse($this->date)->translatedFormat('d F Y'),

            'monthly' => Carbon::create()
                ->day($this->day)
                ->translatedFormat('d F'),

            'yearly' => Carbon::create()
                ->month($this->month)
                ->day($this->day)
                ->translatedFormat('d F'),

            default => '-',
        };
    }

    /**
     * Scope a query to only include active holidays for a specific branch and date.
     */
    public function scopeForBranchAndDate($query, string $branchId, Carbon $date)
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($branchId) {
                // Kondisi 1: Libur Nasional / Berlaku untuk SEMUA cabang
                $q->doesntHave('branches')
                  // Kondisi 2: Libur Khusus Cabang Tertentu
                  ->orWhereHas('branches', fn ($bQ) => $bQ->where('id', $branchId));
            })
            ->where(function ($q) use ($date) {
                $q->where(fn ($subQ) => $subQ->where('recurrence_type', 'one_time')->whereDate('date', $date))
                  ->orWhere(fn ($subQ) => $subQ->where('recurrence_type', 'monthly')->where('day', $date->day))
                  ->orWhere(fn ($subQ) => $subQ->where('recurrence_type', 'yearly')->where('month', $date->month)->where('day', $date->day));
            });
    }
}
