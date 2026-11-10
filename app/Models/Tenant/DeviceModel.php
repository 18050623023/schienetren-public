<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceModel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'device_type_id',
        'brand_id',
        'name',
        'description',
    ];

    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function compatibleProducts()
    {
        return $this->belongsToMany(Product::class, 'device_model_product');
    }
}
