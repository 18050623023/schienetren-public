<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RepairHandover extends Model
{
    protected $fillable = [
        'repair_id',
        'type',
        'recipient_name',
        'photo_path',
        'signature',
    ];

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    public function repairDevices()
    {
        return $this->hasMany(RepairDevice::class);
    }
}
