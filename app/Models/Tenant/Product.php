<?php

namespace App\Models\Tenant;

use App\Models\Concerns\HasTenantLimits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasTenantLimits;

    use \App\Traits\HasDeletableFiles;

    public array $deletableFileColumns = ['image'];
    public array $deletableFileDirectories = ['images/product'];

    // protected $guarded = [];

    // fillable

    protected $fillable = [
        'code',
        'name',
        'warranty_unit',
        'warranty_value',
        'description',
        'brand_id',
        'unit_id',
        'image',
        'has_sn',
        'has_variant',
        'is_include_service',
        'type',
    ];


    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getCategoryAttribute()
    {
        return $this->categories?->first();
    }

    public function compatibleModels()
    {
        return $this->belongsToMany(DeviceModel::class, 'device_model_product');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function stockOpnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function serials()
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function purcahases()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
    
    public function scopeInStock(Builder $query, ?int $branchId = null, ?int $variantId = null): Builder
    {
        return $query->whereHas('stocks', function (Builder $stockQuery) use ($branchId, $variantId) {
            $stockQuery->where('stock', '>', 0)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($variantId, fn ($q) => $q->where('product_variant_id', $variantId));
        });
    }

    public function getStock(?int $branchId = null, ?int $variantId = null): int
    {
        return (int) $this->stocks()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($variantId, fn ($q) => $q->where('product_variant_id', $variantId))
            ->sum('stock');
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($product) {
            $check = static::checkProductLimit();
            if (!$check->passed) {
                throw new \Exception($check->message);
            }
        });
    }

    public static function checkProductLimit(?self $record = null): \App\DTOs\FeatureCheckResult
    {
        return self::checkFeatureLimit(
            \App\Enums\TenantFeature::MAX_PRODUCT,
            'katalog produk / sparepart',
            null,
            $record
        );
    }
}
