<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'branch_id',
        'product_id',
        'product_variant_id',
        'direction',
        'quantity',
        'note',
        'source_type',
        'source_id',
    ];

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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // product Serial
    public function productSerials()
    {
        return $this->belongsToMany(productSerial::class, 'stock_movement_serials')->withTimestamps();
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from && $to) {
            return $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay(),
            ]);
        }
        if ($from) {
            return $query->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            return $query->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
        }
        return $query;
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->where('branch_id', $branchId);
    }

    public function scopeDirection($query, $direction)
    {
        if (!$direction) {
            return $query;
        }
        return $query->where('direction', $direction);
    }

    public function scopeForProduct($query, $productId)
    {
        if (!$productId) {
            return $query;
        }
        return $query->where('product_id', $productId);
    }
}
