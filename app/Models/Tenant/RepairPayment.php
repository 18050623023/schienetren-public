<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RepairPayment extends Model
{
    protected $fillable = [
        'repair_invoice_id',
        'type',
        'payment_method_id',
        'reference_number',
        'amount',
        'notes',
        'refund_reason',
        'payment_date',
        'created_by',
    ];

    protected static function booted()
    {
        static::observe(\App\Observers\Tenant\RepairPaymentObserver::class);
    }

    public function repairInvoice()
    {
        return $this->belongsTo(RepairInvoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('payment_date', '<=', $to);
        }
        return $query;
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->whereHas('repairInvoice.repair', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeForPaymentMethod($query, $methodId)
    {
        if (!$methodId) {
            return $query;
        }
        return $query->where('payment_method_id', $methodId);
    }

    public function scopeType($query, $type)
    {
        if (!$type) {
            return $query;
        }
        return $query->where('type', $type);
    }
}
