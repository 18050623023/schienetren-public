<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $fillable = [
        'branch_id',
        'product_variant_id',
        'serial_number',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stockMovements()
    {
        return $this->belongsToMany(StockMovement::class,'stock_movement_serials');
    }

}
