<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairTechnicianCommission extends Model
{
    protected $fillable = [
        'repair_technician_id',
        'compensation_component_id',
        'repair_item_id',
        'amount',
        'notes',
    ];

    protected static function booted(): void
    {
        static::saved(function (RepairTechnicianCommission $commission) {
            self::updateCache($commission);
        });

        static::deleted(function (RepairTechnicianCommission $commission) {
            self::updateCache($commission);
        });
    }

    protected static function updateCache(RepairTechnicianCommission $commission): void
    {
        $technician = $commission->repairTechnician;
        if ($technician) {
            $total = $technician->commissions()->sum('amount');
            
            // updateQuietly() digunakan agar tidak memicu event 'saved' lagi pada RepairTechnician
            $technician->updateQuietly([
                'commission_amount' => $total
            ]);
        }
    }

    public function repairTechnician(): BelongsTo
    {
        return $this->belongsTo(RepairTechnician::class);
    }

    public function compensationComponent(): BelongsTo
    {
        return $this->belongsTo(CompensationComponent::class);
    }

    public function repairItem(): BelongsTo
    {
        return $this->belongsTo(RepairItem::class);
    }
}
