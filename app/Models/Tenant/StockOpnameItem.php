<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{


    protected $fillable = [
        'stock_opname_id',
        'branch_id',
        'product_id',
        'product_variant_id',
        'system_qty',
        'actual_qty',
        'difference',
        'serials_found',
        'serials_missing',
        'status'
    ];
    protected $casts = [
        'serials_found' => 'array',
        'serials_missing'   => 'array',
    ];


    protected static function booted()
    {

        static::creating(function ($item) {
            $branchId = $item->stockOpname?->branch_id;

            $systemQty = Stock::where('product_variant_id', $item->product_variant_id)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                // ->where('branch_id', $item->stock_opname->branch_id)
                ->value('stock') ?? 0;

            $item->system_qty = $systemQty;
            $item->difference = ($item->actual_qty ?? 0) - $systemQty;
        });
    }


    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    // public function productSerial()
    // {
    //     return $this->hasMany(ProductSerial::class, 'product_variant_id', 'product_variant_id')
    //     ->whereColumn(
    //         'product_serials.branch_id',
    //         'stock_opname_items.branch_id'
    //     );
    // }

    public function serials()
    {
        return $this->hasMany(StockOpnameItemSerial::class);
    }



}
