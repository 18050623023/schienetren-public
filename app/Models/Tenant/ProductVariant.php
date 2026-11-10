<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;



    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'purchase_price',
        'sale_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productSerial()
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    // stockmovement
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    
    
    public function scopeInStock(Builder $query, ?int $branchId = null): Builder
    {
        return $query->whereHas('stocks', function (Builder $stockQuery) use ($branchId) {
            $stockQuery->where('stock', '>', 0)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        });
    }

    public function getStock(?int $branchId = null): int
    {
        return (int) $this->stocks()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('stock');
    }

}
