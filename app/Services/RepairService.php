<?php

namespace App\Services;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Repair;

class RepairService
{
    protected $repairCode = 'REP-%s-%06d';
    protected $invoiceCode = 'INV-%s-%06d';

    public function generateRepairCode(Branch $branch, bool $lock = false): array
    {
        $query = Repair::query()
            ->where('branch_id', $branch->id);

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastSequence = $query->max('sequence');

        $sequence = ($lastSequence ?? 0) + 1;
        $prefix = rtrim(settingTenant('repair_prefix', 'REP'), '-');

        return [
            // 'code' => sprintf( $this->repairCode, $branch->code, $sequence),
            'code' => sprintf("{$prefix}-%s-%06d", $branch->code, $sequence),
            'sequence' => $sequence,
        ];
    }

    public function generateInvoiceCode(Branch $branch, bool $lock = false): string
    {
        $query = \App\Models\Tenant\RepairInvoice::query()
            ->whereHas('repair', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            });

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastInvoice = $query->max('invoice_number');

        $sequence = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice);
            $sequence = (int) end($parts) + 1;
        }

        // return sprintf($this->invoiceCode, $branch->code, $sequence);
        $prefix = rtrim(settingTenant('invoice_prefix', 'INV'), '-');

        return sprintf("{$prefix}-%s-%06d", $branch->code, $sequence);
    }
}
