<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RepairWarrantyClaim extends Model
{
    protected $fillable = [
        'repair_item_warranty_id',
        'new_repair_id',
        'claim_date',
        'problems',
        'solution',
        'notes',
        'refund_amount',
        'refund_payment_id',
        'return_item_action',
        'status',
        'handled_by',
    ];

    public function refundPayment()
    {
        return $this->belongsTo(RepairPayment::class, 'refund_payment_id');
    }

    public function repairItemWarranty()
    {
        return $this->belongsTo(RepairItemWarranty::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class);
    }

    public function newRepair()
    {
        return $this->belongsTo(Repair::class);
    }

    protected static function booted(): void
    {
        static::updated(function (RepairWarrantyClaim $claim) {
            // Ponytail: Poin 2 - Logika Penolakan Klaim
            // Jika klaim ditolak, otomatis batalkan tiket RMA yang menggantung (jika ada)
            if ($claim->wasChanged('status') && $claim->status === 'rejected') {
                if ($claim->new_repair_id) {
                    $repair = \App\Models\Tenant\Repair::find($claim->new_repair_id);
                    if ($repair && $repair->status !== \App\Enums\RepairStatus::Cancel) {
                        $repair->update([
                            'status' => \App\Enums\RepairStatus::Cancel,
                            'canceled_at' => now(),
                            'canceled_by' => \Illuminate\Support\Facades\Auth::id(),
                            'notes' => trim($repair->notes . "\n\n[Sistem] Tiket dibatalkan otomatis karena klaim garansi ditolak."),
                        ]);
                    }
                }
            }
        });
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('claim_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('claim_date', '<=', $to);
        }
        return $query;
    }

    public function scopeStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }
        return $query->where('status', $status);
    }

    public function scopeForBranch($query, $branchId)
    {
        if (!$branchId) {
            return $query;
        }
        return $query->where(function ($q) use ($branchId) {
            $q->whereHas('newRepair', function ($qr) use ($branchId) {
                $qr->where('branch_id', $branchId);
            })->orWhereHas('repairItemWarranty.repairItem.repairDevice.repair', function ($qr) use ($branchId) {
                $qr->where('branch_id', $branchId);
            });
        });
    }
}
