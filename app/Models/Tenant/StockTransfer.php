<?php

namespace App\Models\Tenant;

use App\Enums\TenantFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'from_branch_id',
        'to_branch_id',
        'date',
        'status',
        'notes',
        'created_by',
        'shipped_by',
        'received_by',
        'canceled_by',
        'shipped_at',
        'received_at',
        'canceled_at',
    ];

    protected static function booted()
    {
        static::creating(function ($transfer) {
            if (!tenant()->hasFeatureCode(TenantFeature::STOCK_TRANSFER)) {
                throw new \Exception('Fitur Mutasi Stok tidak aktif pada paket langganan Anda.');
            }

            if (Branch::where('is_active', true)->count() <= 1) {
                throw new \Exception('Mutasi stok memerlukan minimal 2 cabang aktif.');
            }

            if ($transfer->from_branch_id && $transfer->to_branch_id && $transfer->from_branch_id === $transfer->to_branch_id) {
                throw new \Exception('Cabang asal dan cabang tujuan tidak boleh sama.');
            }
        });

        static::updating(function ($transfer) {
            // Kita cek jika status sebelumnya in_transit dan tidak sedang diubah statusnya,
            // maka data utamanya tidak boleh berubah.
            if ($transfer->getOriginal('status') === 'in_transit' && $transfer->status === 'in_transit') {
                if ($transfer->isDirty(['from_branch_id', 'to_branch_id', 'date', 'code'])) {
                    throw new \Exception('Data utama mutasi tidak bisa diubah saat barang sedang dalam pengiriman.');
                }
            }
        });
    }


    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canceledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by');
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
        return $query->where(function ($q) use ($branchId) {
            $q->where('from_branch_id', $branchId)
              ->orWhere('to_branch_id', $branchId);
        });
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }
}
