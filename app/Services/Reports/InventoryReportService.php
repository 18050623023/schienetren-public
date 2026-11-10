<?php

namespace App\Services\Reports;

use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockTransfer;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReportService
{
    /**
     * 1. Laporan Valuasi & Ringkasan Stok Gudang
     */
    public function getStockValuationReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $productId = $filters['product_id'] ?? null;

        $query = Stock::query()
            ->forBranch($branchId)
            ->forProduct($productId)
            ->with([
                'product.brand',
                'product.categories',
                'product.unit',
                'productVariant',
                'branch',
            ]);

        $stocks = $query->get();

        $totalSkus = $stocks->count();
        $totalUnits = (int) $stocks->sum('stock');
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $totalCostValue = 0;
        $totalSaleValue = 0;
        $rows = [];
        foreach ($stocks as $item) {
            $qty = (int) $item->stock;
            $purchasePrice = (float) ($item->productVariant?->purchase_price ?? 0);
            $salePrice = (float) ($item->productVariant?->sale_price ?? 0);

            $costVal = $qty * $purchasePrice;
            $saleVal = $qty * $salePrice;
            $margin = $saleVal - $costVal;

            $totalCostValue += $costVal;
            $totalSaleValue += $saleVal;

            if ($qty <= 0) {
                $outOfStockCount++;
            } elseif ($qty <= 5) {
                $lowStockCount++;
            }

            $categoryNames = $item->product?->categories?->pluck('name')->filter()->implode(', ');

            $rows[] = [
                'id' => $item->id,
                'sku' => $item->productVariant?->sku ?? '-',
                'product_name' => ($item->product?->name ?? 'Produk') . ' ' . ($item->productVariant?->name ?? ''),
                'brand' => $item->product?->brand?->name ?? '-',
                'category' => $categoryNames ?: '-',
                'branch' => $item->branch?->name ?? '-',
                'stock' => $qty,
                'unit' => $item->product?->unit?->name ?? 'Pcs',
                'purchase_price' => $purchasePrice,
                'sale_price' => $salePrice,
                'total_cost' => $costVal,
                'total_sale' => $saleVal,
                'potential_margin' => $margin,
            ];
        }

        // Urutkan berdasarkan nama produk
        usort($rows, fn ($a, $b) => strcmp($a['product_name'], $b['product_name']));

        return [
            'kpi' => [
                'total_skus' => $totalSkus,
                'total_units' => $totalUnits,
                'total_cost_value' => $totalCostValue,
                'total_sale_value' => $totalSaleValue,
                'potential_margin' => $totalSaleValue - $totalCostValue,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
            ],
            'rows' => $rows,
            'filters' => [
                'branch_id' => $branchId,
                'product_id' => $productId,
            ],
        ];
    }

    /**
     * 2. Laporan Kartu Stok & Mutasi Barang
     */
    public function getStockMovementReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $productId = $filters['product_id'] ?? null;
        $direction = $filters['direction'] ?? null;

        $query = StockMovement::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->direction($direction)
            ->forProduct($productId)
            ->with([
                'product.brand',
                'productVariant',
                'branch',
                'source',
            ]);

        $movements = $query->orderBy('created_at', 'desc')->get();

        $totalQtyIn = (int) $movements->where('direction', 'in')->sum('quantity');
        $totalQtyOut = (int) $movements->where('direction', 'out')->sum('quantity');

        return [
            'kpi' => [
                'total_movements' => $movements->count(),
                'total_qty_in' => $totalQtyIn,
                'total_qty_out' => $totalQtyOut,
                'net_flow' => $totalQtyIn - $totalQtyOut,
            ],
            'movements' => $movements,
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'product_id' => $productId,
                'direction' => $direction,
            ],
        ];
    }

    /**
     * 3. Laporan Selisih Stock Opname
     */
    public function getStockOpnameReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $status = $filters['status'] ?? null;

        $query = StockOpname::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->status($status)
            ->with([
                'branch',
                'createdBy',
                'postedBy',
                'items.product',
                'items.productVariant',
            ]);

        $opnames = $query->orderBy('date', 'desc')->get();

        $totalItemsAudited = 0;
        $totalDeficitQty = 0;
        $totalSurplusQty = 0;
        $totalLossValue = 0;

        foreach ($opnames as $opname) {
            foreach ($opname->items as $item) {
                $totalItemsAudited++;
                $diff = (int) $item->difference;
                $cost = (float) ($item->productVariant?->purchase_price ?? 0);

                if ($diff < 0) {
                    $totalDeficitQty += abs($diff);
                    $totalLossValue += abs($diff) * $cost;
                } elseif ($diff > 0) {
                    $totalSurplusQty += $diff;
                }
            }
        }

        return [
            'kpi' => [
                'total_audits' => $opnames->count(),
                'total_items_audited' => $totalItemsAudited,
                'total_deficit_qty' => $totalDeficitQty,
                'total_surplus_qty' => $totalSurplusQty,
                'total_loss_value' => $totalLossValue,
            ],
            'opnames' => $opnames,
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
            ],
        ];
    }

    /**
     * 4. Laporan Transfer Antar Cabang
     */
    public function getStockTransferReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $status = $filters['status'] ?? null;

        $query = StockTransfer::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->status($status)
            ->with([
                'fromBranch',
                'toBranch',
                'createdBy',
                'shippedBy',
                'receivedBy',
                'items.product',
                'items.productVariant',
            ]);

        $transfers = $query->orderBy('date', 'desc')->get();

        $inTransitCount = $transfers->where('status', 'in_transit')->count();
        $receivedCount = $transfers->where('status', 'received')->count();
        $canceledCount = $transfers->where('status', 'canceled')->count();
        $totalItemsCount = (int) $transfers->flatMap->items->sum('quantity');

        return [
            'kpi' => [
                'total_transfers' => $transfers->count(),
                'in_transit_count' => $inTransitCount,
                'received_count' => $receivedCount,
                'canceled_count' => $canceledCount,
                'total_items_count' => $totalItemsCount,
            ],
            'transfers' => $transfers,
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
            ],
        ];
    }

    /**
     * 5. Laporan Pengadaan & Pembelian Supplier
     */
    public function getPurchasingReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $supplierId = $filters['supplier_id'] ?? null;
        $status = $filters['status'] ?? null;

        $query = PurchaseOrder::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forSupplier($supplierId)
            ->status($status)
            ->with([
                'supplier',
                'branch',
                'items.product',
                'items.variant',
                'goodsReceipts',
            ]);

        $purchases = $query->orderBy('date', 'desc')->get();

        $totalSpend = (float) $purchases->sum('total');
        $totalPaid = (float) $purchases->sum('paid_amount');
        $totalDebt = (float) $purchases->sum('remaining_amount');
        $receivedCount = $purchases->where('status', 'received')->count();
        $orderedCount = $purchases->where('status', 'ordered')->count();

        return [
            'kpi' => [
                'total_pos' => $purchases->count(),
                'total_spend' => $totalSpend,
                'total_paid' => $totalPaid,
                'total_debt' => $totalDebt,
                'received_count' => $receivedCount,
                'ordered_count' => $orderedCount,
            ],
            'purchases' => $purchases,
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'supplier_id' => $supplierId,
                'status' => $status,
            ],
        ];
    }

    /**
     * Streaming CSV Exports
     */
    public function exportStockValuationCsv(array $filters): StreamedResponse
    {
        $report = $this->getStockValuationReport($filters);
        $fileName = 'laporan-valuasi-stok-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'SKU',
                'Nama Produk & Varian',
                'Brand',
                'Kategori',
                'Cabang',
                'Sisa Stok',
                'Satuan',
                'Harga Beli Modal (HPP)',
                'Harga Jual',
                'Total Nilai Modal Aset',
                'Total Potensi Nilai Jual',
                'Estimasi Laba Kotor',
            ]);

            $no = 1;
            foreach ($report['rows'] as $r) {
                fputcsv($handle, [
                    $no++,
                    $r['sku'],
                    $r['product_name'],
                    $r['brand'],
                    $r['category'],
                    $r['branch'],
                    $r['stock'],
                    $r['unit'],
                    $r['purchase_price'],
                    $r['sale_price'],
                    $r['total_cost'],
                    $r['total_sale'],
                    $r['potential_margin'],
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportStockMovementCsv(array $filters): StreamedResponse
    {
        $report = $this->getStockMovementReport($filters);
        $fileName = 'laporan-mutasi-stok-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Tanggal',
                'Produk & Varian',
                'Cabang',
                'Arah',
                'Jumlah (Qty)',
                'Sumber Transaksi',
                'Catatan',
            ]);

            $no = 1;
            foreach ($report['movements'] as $m) {
                fputcsv($handle, [
                    $no++,
                    $m->created_at?->format('d/m/Y H:i') ?? '-',
                    ($m->product?->name ?? 'Produk') . ' ' . ($m->productVariant?->name ?? ''),
                    $m->branch?->name ?? '-',
                    strtoupper($m->direction),
                    $m->quantity,
                    class_basename($m->source_type ?? 'Manual'),
                    $m->note ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportStockOpnameCsv(array $filters): StreamedResponse
    {
        $report = $this->getStockOpnameReport($filters);
        $fileName = 'laporan-stock-opname-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Tanggal Audit',
                'Kode Opname',
                'Cabang',
                'Produk & Varian',
                'Stok Sistem',
                'Hitungan Fisik',
                'Selisih (Qty)',
                'Harga Modal Satuan',
                'Estimasi Nilai Selisih',
                'Status',
            ]);

            $no = 1;
            foreach ($report['opnames'] as $op) {
                foreach ($op->items as $item) {
                    $diff = (int) $item->difference;
                    $cost = (float) ($item->productVariant?->purchase_price ?? 0);
                    fputcsv($handle, [
                        $no++,
                        $op->date?->format('d/m/Y') ?? '-',
                        $op->reference ?? '-',
                        $op->branch?->name ?? '-',
                        ($item->product?->name ?? '-') . ' ' . ($item->productVariant?->name ?? ''),
                        $item->system_qty,
                        $item->actual_qty,
                        $diff,
                        $cost,
                        $diff * $cost,
                        strtoupper($op->status),
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportStockTransferCsv(array $filters): StreamedResponse
    {
        $report = $this->getStockTransferReport($filters);
        $fileName = 'laporan-transfer-stok-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Kode Mutasi',
                'Tanggal',
                'Cabang Pengirim',
                'Cabang Penerima',
                'Total Macam Barang',
                'Total Qty Barang',
                'Status',
            ]);

            $no = 1;
            foreach ($report['transfers'] as $t) {
                fputcsv($handle, [
                    $no++,
                    $t->code,
                    $t->date,
                    $t->fromBranch?->name ?? '-',
                    $t->toBranch?->name ?? '-',
                    $t->items->count(),
                    $t->items->sum('quantity'),
                    strtoupper($t->status),
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPurchasingCsv(array $filters): StreamedResponse
    {
        $report = $this->getPurchasingReport($filters);
        $fileName = 'laporan-pengadaan-po-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Kode PO',
                'Tanggal',
                'Supplier',
                'Cabang',
                'Total Belanja',
                'Sudah Dibayar',
                'Sisa Hutang',
                'Status',
            ]);

            $no = 1;
            foreach ($report['purchases'] as $p) {
                fputcsv($handle, [
                    $no++,
                    $p->code,
                    $p->date,
                    $p->supplier?->name ?? '-',
                    $p->branch?->name ?? '-',
                    $p->total,
                    $p->paid_amount,
                    $p->remaining_amount,
                    strtoupper($p->status),
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
