<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'repair_id',
        'invoice_date',
        'invoice_number',
        'due_date',
        'subtotal',
        'discount_id',
        'discount_type',
        'discount_value',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'shipping_fee',
        'grand_total',
        'paid_amount',
        'remaining_amount',
        'status',
    ];

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function repairPayments()
    {
        return $this->hasMany(RepairPayment::class);
    }

    /**
     * Cari invoice untuk repair ini, atau buat baru (kosongan) jika belum ada.
     */
    public static function findOrCreateForRepair(Repair $repair)
    {
        $invoice = $repair->repairInvoice;
        if (!$invoice) {
            $branch = \App\Models\Tenant\Branch::find($repair->branch_id);
            $invoiceData = [
                'repair_id' => $repair->id,
                'invoice_date' => now(),
                'subtotal' => 0,
                'grand_total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => 'unpaid',
            ];
            
            if ($branch) {
                $invoiceData['invoice_number'] = app(\App\Services\RepairService::class)->generateInvoiceCode($branch, true);
            }
            
            $invoice = self::create($invoiceData);
            
            // Reload the relationship so it's instantly available in memory
            $repair->setRelation('repairInvoice', $invoice);
        }
        
        return $invoice;
    }

    /**
     * Hitung ulang status dan sisa tagihan berdasarkan grand_total dan total pembayaran riil.
     */
    public function recalculateTotals(?float $newGrandTotal = null)
    {
        if ($newGrandTotal !== null) {
            $this->subtotal = $newGrandTotal;
            $this->grand_total = $newGrandTotal;
        }

        // Ambil riil pembayaran dari database untuk menghindari desync
        // $totalPaid = $this->repairPayments()->sum('amount');
        // $this->paid_amount = $totalPaid;

        // Ambil riil pembayaran dan refund dari database untuk menghindari desync
        $totalIn = (float) $this->repairPayments()->where(fn($q) => $q->whereNull('type')->orWhere('type', '!=', 'refund'))->sum('amount');
        $totalRefund = (float) $this->repairPayments()->where('type', 'refund')->sum('amount');
        $this->paid_amount = max(0, $totalIn - $totalRefund);
        
        // $this->remaining_amount = $this->grand_total - $this->paid_amount;
        $this->remaining_amount = max(0, $this->grand_total - $this->paid_amount);
        
        // if ($this->remaining_amount <= 0 && $this->grand_total > 0) {
        if ($totalRefund > 0 && $this->paid_amount == 0 && $totalIn > 0) {
            $this->status = 'refunded';
        } elseif ($this->remaining_amount <= 0 && $this->grand_total > 0) {
            $this->status = 'paid';
            $this->remaining_amount = 0; // hindari sisa negatif karena presisi
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
        return $this;
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('invoice_date', '<=', $to);
        }
        return $query;
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->whereHas('repair', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeUnpaid($query)
    {
        return $query->where('remaining_amount', '>', 0)
                     ->where('status', '!=', 'refunded');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                     ->where('remaining_amount', '>', 0)
                     ->where('status', '!=', 'refunded');
    }
}
