<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RepairItemWarranty extends Model
{
    protected $fillable = [
        'repair_item_id',
        'duration_value',
        'duration_unit',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    public function repairItem()
    {
        return $this->belongsTo(RepairItem::class);
    }

    public function repairWarrantyClaims()
    {
        return $this->hasMany(RepairWarrantyClaim::class);
    }
}
