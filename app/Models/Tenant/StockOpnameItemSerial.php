<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItemSerial extends Model
{
    protected $fillable = [
        'stock_opname_item_id',
        'product_id',
        'product_variant_id',
        'serial_number',
        'product_serial_id',
        'status',
        'note',
    ];

    public function stockOpnameItem()
    {
        return $this->belongsTo(StockOpnameItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productSerial()
    {
        return $this->belongsTo(ProductSerial::class);
    }

}
