<?php

namespace App\Models\Tenant;

use App\Enums\TenantFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PurchaseOrder extends Model
{
    use SoftDeletes;



    protected $fillable = [
        'code',
        'discount',
        'paid_amount',
        'remaining_amount',
        'branch_id',
        'supplier_id',
        'created_by',
        'canceled_by',
        'confirmed_by',
        'canceled_at',
        'confirmed_at',
        'total',
        'payment_method',
        'date',
        'status',
        'notes',

        'attachment',
    ];

    protected static function booted()
    {
        static::creating(function ($po) {
            if (function_exists('tenant') && tenant() && !tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER)) {
                throw new \Exception('Fitur Purchase Order tidak aktif pada paket langganan Anda.');
            }
        });

        static::observe(\App\Observers\Tenant\PurchaseOrderObserver::class);
    }

    public function canDelete()
    {
        return $this->status === 'canceled';
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cenceledBy()
    {
        return $this->belongsTo(User::class, 'cenceled_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function itemsWithVariant()
    {
        return $this->hasMany(PurchaseOrderItem::class)->with('variant');
    }

    public function itemsWithProduct()
    {
        return $this->hasMany(PurchaseOrderItem::class)->with('product');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipts::class);
    }

    /**
     * Query Scopes for Reporting & Filtering
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from && $to) {
            return $query->whereBetween('date', [
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay(),
            ]);
        }
        if ($from) {
            return $query->where('date', '>=', \Carbon\Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            return $query->where('date', '<=', \Carbon\Carbon::parse($to)->endOfDay());
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

    public function scopeForSupplier($query, $supplierId)
    {
        if (!$supplierId) {
            return $query;
        }
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('remaining_amount', '>', 0)
            ->where('status', '!=', 'canceled');
    }
}
