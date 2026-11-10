<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'branch_id',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->where('branch_id', $branchId);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query, $threshold = 5)
    {
        return $query->where('stock', '<=', $threshold);
    }

    public function scopeForProduct($query, $productId)
    {
        if (!$productId) {
            return $query;
        }
        return $query->where('product_id', $productId);
    }
}
