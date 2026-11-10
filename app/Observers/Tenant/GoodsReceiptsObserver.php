<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\GoodsReceipts;
use App\Services\FinanceAccountingService;

class GoodsReceiptsObserver
{
    protected FinanceAccountingService $financeService;

    public function __construct(FinanceAccountingService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function created(GoodsReceipts $goodsReceipt)
    {
        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE)) {
            return;
        }
        // Lacak Branch ID dari PO
        $branchId = $goodsReceipt->purchaseOrder->branch_id ?? null;
        if (!$branchId) return;

        // Ambil pemetaan akun
        $inventoryAccountId = settingTenant('finance_account_inventory', 2);
        $apAccountId = settingTenant('finance_account_ap', 5);

        // Ambil total nilai PO
        $totalAmount = $goodsReceipt->purchaseOrder->total ?? 0;

        if ($totalAmount <= 0) return;

        $poCode = $goodsReceipt->purchaseOrder->code ?? '';
                                                                                                                                                                    
        $items = [
            [
                'finance_account_id' => $inventoryAccountId,
                'debit' => $totalAmount,
                'credit' => 0,
                'description' => 'Persediaan Masuk dr PO ' . $poCode,
            ],
            [
                'finance_account_id' => $apAccountId,
                'debit' => 0,
                'credit' => $totalAmount,
                'description' => 'Hutang Pembelian (AP)',
            ]
        ];

        $this->financeService->createJournalEntry(
            $branchId,
            $goodsReceipt->date ?? now()->toDateString(),
            'Penerimaan Barang PO ' . $poCode,
            $items,
            $goodsReceipt,
            $goodsReceipt->received_by
        );
    }
}
