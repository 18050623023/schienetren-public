<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'name',
        'description',
        'type',
    ];


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function repairDevices()
    {
        return $this->hasMany(RepairDevice::class);
    }

    public function deviceModels()
    {
        return $this->hasMany(DeviceModel::class);
    }

    public function scopeForProduct($query)
    {
        return $query->whereIn('type', ['product', 'both']);
    }

    public function scopeForDevice($query)
    {
        return $query->whereIn('type', ['device', 'both']);
    }
}
