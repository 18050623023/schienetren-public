<?php

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'client_id',
        'cashier_id',
        'cash_shift_id',
        'invoice_number',
        'date',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_method_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(FinanceCashShift::class, 'cash_shift_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function itemSerials(): HasManyThrough
    {
        return $this->hasManyThrough(SaleItemSerial::class, SaleItem::class);
    }

    /**
     * Generate unique POS invoice number with branch code & sequence
     */
    public static function generateInvoiceNumber(Branch $branch, bool $lock = false): string
    {
        $prefix = rtrim(settingTenant('pos_prefix', 'POS'), '-');
        $branchCode = $branch->code ?: 'BR' . $branch->id;
        $dateStr = now()->format('Ymd');
        $pattern = "{$prefix}-{$branchCode}-{$dateStr}-%";

        $query = static::where('branch_id', $branch->id)
            ->where('invoice_number', 'like', $pattern);

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastNumber = $query->max('invoice_number');

        $sequence = 1;
        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf("{$prefix}-%s-%s-%04d", $branchCode, $dateStr, $sequence);
    }

    /**
     * Query Scopes
     */
    public function scopeBetweenDates(Builder $query, $from, $to): Builder
    {
        if ($from && $to) {
            return $query->whereBetween('date', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ]);
        }
        if ($from) {
            return $query->where('date', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            return $query->where('date', '<=', Carbon::parse($to)->endOfDay());
        }
        return $query;
    }

    public function scopeForBranch(Builder $query, $branchId): Builder
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }

    public function scopeForCashier(Builder $query, $cashierId): Builder
    {
        return $cashierId ? $query->where('cashier_id', $cashierId) : $query;
    }

    public function scopeForPaymentMethod(Builder $query, $paymentMethodId): Builder
    {
        return $paymentMethodId ? $query->where('payment_method_id', $paymentMethodId) : $query;
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
