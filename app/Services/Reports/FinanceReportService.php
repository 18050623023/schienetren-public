<?php

namespace App\Services\Reports;

use App\Models\Tenant\FinanceExpense;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\RepairInvoice;
use App\Models\Tenant\RepairPayment;
use App\Models\Tenant\Sale;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportService
{
    /**
     * 1. Laporan Arus Kas Masuk & Keluar (Cash Flow)
     */
    public function getCashFlowReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $paymentMethodId = $filters['payment_method_id'] ?? null;

        // 1. Kas Masuk: Pembayaran invoice servis (non-refund)
        $cashInQuery = RepairPayment::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forPaymentMethod($paymentMethodId)
            ->where(function ($q) {
                $q->whereNull('type')->orWhere('type', '!=', 'refund');
            })
            ->with([
                'repairInvoice.repair.client',
                'repairInvoice.repair.branch',
                'paymentMethod',
            ]);

        $cashInPayments = $cashInQuery->get();

        // 1.b Kas Masuk: Penjualan Langsung POS Retail
        $posSaleQuery = Sale::query()
            ->completed()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forPaymentMethod($paymentMethodId)
            ->with([
                'client',
                'branch',
                'paymentMethod',
            ]);

        $posSales = $posSaleQuery->get();

        // 2. Kas Keluar: Beban operasional toko (FinanceExpense)
        $expenseQuery = FinanceExpense::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forPaymentMethod($paymentMethodId)
            ->with([
                'account',
                'paymentMethod',
                'branch',
                'creator',
            ]);

        $expenses = $expenseQuery->get();

        // 3. Kas Keluar: Pengembalian dana klaim garansi (Refunds)
        $refundQuery = RepairPayment::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forPaymentMethod($paymentMethodId)
            ->where('type', 'refund')
            ->with([
                'repairInvoice.repair.client',
                'repairInvoice.repair.branch',
                'paymentMethod',
            ]);

        $refunds = $refundQuery->get();

        $totalCashIn = (float) $cashInPayments->sum('amount') + (float) $posSales->sum('paid_amount');
        $totalExpenses = (float) $expenses->sum('amount');
        $totalRefunds = (float) $refunds->sum('amount');
        $totalCashOut = $totalExpenses + $totalRefunds;
        $netCashFlow = $totalCashIn - $totalCashOut;

        // Gabungkan transaksi menjadi timeline kronologis
        $transactions = [];

        foreach ($cashInPayments as $p) {
            $date = $p->payment_date ?: $p->created_at;
            $clientName = $p->repairInvoice?->repair?->client?->name ?? 'Pelanggan';
            $repairCode = $p->repairInvoice?->repair?->code ?? '-';

            $transactions[] = [
                'type' => 'in',
                'type_label' => 'Kas Masuk (Servis)',
                'date' => Carbon::parse($date)->format('d/m/Y H:i'),
                'raw_date' => Carbon::parse($date),
                'reference' => $p->reference_number ?: "PAY-{$p->id}",
                'description' => "Pembayaran Servis {$repairCode} - {$clientName}",
                'branch' => $p->repairInvoice?->repair?->branch?->name ?? '-',
                'method' => $p->paymentMethod?->name ?? 'Tunai',
                'amount' => (float) $p->amount,
            ];
        }

        foreach ($posSales as $s) {
            $date = $s->date ?: $s->created_at;
            $clientName = $s->client?->name ?? 'Walk-in Customer';

            $transactions[] = [
                'type' => 'in',
                'type_label' => 'Kas Masuk (POS)',
                'date' => Carbon::parse($date)->format('d/m/Y H:i'),
                'raw_date' => Carbon::parse($date),
                'reference' => $s->invoice_number,
                'description' => "Penjualan Langsung POS - {$clientName}",
                'branch' => $s->branch?->name ?? '-',
                'method' => $s->paymentMethod?->name ?? 'Tunai',
                'amount' => (float) $s->paid_amount,
            ];
        }

        foreach ($expenses as $e) {
            $catName = $e->account?->name ?? 'Operasional';

            $transactions[] = [
                'type' => 'out',
                'type_label' => 'Kas Keluar',
                'date' => Carbon::parse($e->date)->format('d/m/Y'),
                'raw_date' => Carbon::parse($e->date),
                'reference' => $e->reference_number ?: "EXP-{$e->id}",
                'description' => "[{$catName}] " . ($e->description ?: 'Beban Operasional Toko'),
                'branch' => $e->branch?->name ?? '-',
                'method' => $e->paymentMethod?->name ?? 'Kas Toko',
                'amount' => (float) $e->amount,
            ];
        }

        foreach ($refunds as $rf) {
            $date = $rf->payment_date ?: $rf->created_at;
            $clientName = $rf->repairInvoice?->repair?->client?->name ?? 'Pelanggan';
            $repairCode = $rf->repairInvoice?->repair?->code ?? '-';

            $transactions[] = [
                'type' => 'out',
                'type_label' => 'Refund Pelanggan',
                'date' => Carbon::parse($date)->format('d/m/Y H:i'),
                'raw_date' => Carbon::parse($date),
                'reference' => $rf->reference_number ?: "REF-{$rf->id}",
                'description' => "Refund Servis {$repairCode} - {$clientName} ({$rf->refund_reason})",
                'branch' => $rf->repairInvoice?->repair?->branch?->name ?? '-',
                'method' => $rf->paymentMethod?->name ?? 'Tunai',
                'amount' => (float) $rf->amount,
            ];
        }

        // Urutkan transaksi dari yang terbaru
        usort($transactions, fn($a, $b) => $b['raw_date'] <=> $a['raw_date']);

        // Rincian berdasarkan metode pembayaran
        $methodBreakdown = [];
        foreach ($transactions as $t) {
            $m = $t['method'];
            if (!isset($methodBreakdown[$m])) {
                $methodBreakdown[$m] = ['in' => 0, 'out' => 0, 'net' => 0];
            }
            if ($t['type'] === 'in') {
                $methodBreakdown[$m]['in'] += $t['amount'];
            } else {
                $methodBreakdown[$m]['out'] += $t['amount'];
            }
            $methodBreakdown[$m]['net'] = $methodBreakdown[$m]['in'] - $methodBreakdown[$m]['out'];
        }

        return [
            'summary' => [
                'total_cash_in' => $totalCashIn,
                'total_cash_out' => $totalCashOut,
                'total_expenses' => $totalExpenses,
                'total_refunds' => $totalRefunds,
                'net_cash_flow' => $netCashFlow,
                'total_transactions' => count($transactions),
            ],
            'methods' => $methodBreakdown,
            'rows' => $transactions,
        ];
    }

    /**
     * 2. Laporan Laba Rugi Operasional Sederhana (Simple P&L)
     */
    public function getProfitLossReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        // 1. Pendapatan Operasional dari Invoice Servis
        $invoiceQuery = RepairInvoice::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->where('status', '!=', 'refunded')
            ->with([
                'repair.repairDevices.repairItems.productVariant',
            ]);

        $invoices = $invoiceQuery->get();

        $totalRevenue = (float) $invoices->sum('grand_total');
        $totalPaidRevenue = (float) $invoices->sum('paid_amount');
        $totalCogs = 0; // HPP Modal Suku Cadang Terjual

        foreach ($invoices as $inv) {
            $devices = $inv->repair?->repairDevices ?? collect();
            foreach ($devices as $dev) {
                foreach ($dev->repairItems as $item) {
                    $qty = (int) ($item->qty ?? 1);
                    $purchasePrice = (float) ($item->productVariant?->purchase_price ?? 0);
                    $totalCogs += ($qty * $purchasePrice);
                }
            }
        }

        // 1.b Pendapatan dan HPP dari Penjualan Langsung POS
        $posSaleQuery = Sale::query()
            ->completed()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->with(['items']);

        $posSales = $posSaleQuery->get();
        $posRevenue = (float) $posSales->sum('grand_total');
        $posPaidRevenue = (float) $posSales->sum('paid_amount');
        $totalRevenue += $posRevenue;
        $totalPaidRevenue += $posPaidRevenue;

        foreach ($posSales as $s) {
            foreach ($s->items as $sItem) {
                $qty = (int) ($sItem->qty ?? 1);
                $costPrice = (float) ($sItem->cost_price ?? 0);
                $totalCogs += ($qty * $costPrice);
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // 2. Beban Operasional Toko
        $expenseQuery = FinanceExpense::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->with('account');

        $expenses = $expenseQuery->get();
        $totalOperatingExpenses = (float) $expenses->sum('amount');

        $netProfit = $grossProfit - $totalOperatingExpenses;
        $netMarginPct = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // Distribusi Beban Operasional per Akun / Kategori
        $expenseCategories = [];
        foreach ($expenses->groupBy(fn($e) => $e->account?->name ?? 'Lain-lain') as $catName => $items) {
            $catSum = (float) $items->sum('amount');
            $catPct = $totalOperatingExpenses > 0 ? round(($catSum / $totalOperatingExpenses) * 100, 1) : 0;
            $expenseCategories[] = [
                'name' => $catName,
                'amount' => $catSum,
                'percentage' => $catPct,
            ];
        }
        usort($expenseCategories, fn($a, $b) => $b['amount'] <=> $a['amount']);

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_paid_revenue' => $totalPaidRevenue,
                'total_cogs' => $totalCogs,
                'gross_profit' => $grossProfit,
                'gross_margin_pct' => $grossMarginPct,
                'total_operating_expenses' => $totalOperatingExpenses,
                'net_profit' => $netProfit,
                'net_margin_pct' => $netMarginPct,
            ],
            'expense_breakdown' => $expenseCategories,
        ];
    }

    /**
     * 3. Laporan Buku Piutang Pelanggan (Customer Receivables)
     */
    public function getCustomerReceivablesReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $query = RepairInvoice::query()
            ->unpaid()
            ->forBranch($branchId)
            ->betweenDates($dateFrom, $dateTo)
            ->with([
                'repair.client',
                'repair.branch',
                'repairPayments',
            ])
            ->latest('invoice_date');

        $invoices = $query->get();

        $totalReceivables = (float) $invoices->sum('remaining_amount');
        $totalInvoiceAmount = (float) $invoices->sum('grand_total');
        $totalPaidAmount = (float) $invoices->sum('paid_amount');
        $overdueCount = 0;
        $overdueAmount = 0;

        $today = Carbon::today();
        $rows = [];

        foreach ($invoices as $inv) {
            $rem = (float) $inv->remaining_amount;
            $client = $inv->repair?->client;
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date) : null;
            $isOverdue = $dueDate && $dueDate->lt($today);

            if ($isOverdue) {
                $overdueCount++;
                $overdueAmount += $rem;
            }

            // WhatsApp formatting (0812 -> 62812)
            $rawWa = $client?->whatsapp ?: $client?->phone;
            $cleanWa = preg_replace('/[^0-9]/', '', (string) $rawWa);
            if (str_starts_with($cleanWa, '0')) {
                $cleanWa = '62' . substr($cleanWa, 1);
            }

            $waMessage = rawurlencode(
                "Halo Kak {$client?->name},\n\n" .
                "Kami dari " . (tenant('name') ?? 'Service Center') . " ingin menginformasikan tagihan servis tiket {$inv->repair?->code} sebesar Rp " . number_format($rem, 0, ',', '.') . " yang belum lunas.\n" .
                "Mohon konfirmasi atau pelunasan. Terima kasih!"
            );

            $rows[] = [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number ?: "INV-{$inv->id}",
                'repair_code' => $inv->repair?->code ?? '-',
                'client_name' => $client?->name ?? 'Pelanggan Umum',
                'client_phone' => $client?->phone ?: '-',
                'client_whatsapp' => $client?->whatsapp ?: '-',
                'wa_link' => $cleanWa ? "https://wa.me/{$cleanWa}?text={$waMessage}" : null,
                'branch_name' => $inv->repair?->branch?->name ?? '-',
                'invoice_date' => $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('d/m/Y') : '-',
                'due_date' => $dueDate ? $dueDate->format('d/m/Y') : '-',
                'is_overdue' => $isOverdue,
                'days_overdue' => $isOverdue ? $dueDate->diffInDays($today) : 0,
                'grand_total' => (float) $inv->grand_total,
                'paid_amount' => (float) $inv->paid_amount,
                'remaining_amount' => $rem,
                'status' => $inv->status,
            ];
        }

        // Urutkan yang jatuh tempo (overdue) paling lama di posisi paling atas
        usort($rows, function ($a, $b) {
            if ($a['is_overdue'] === $b['is_overdue']) {
                return $b['remaining_amount'] <=> $a['remaining_amount'];
            }
            return $a['is_overdue'] ? -1 : 1;
        });

        $recoveryRate = $totalInvoiceAmount > 0 ? round(($totalPaidAmount / $totalInvoiceAmount) * 100, 1) : 0;

        return [
            'summary' => [
                'total_receivables' => $totalReceivables,
                'total_invoices_count' => count($rows),
                'total_invoice_amount' => $totalInvoiceAmount,
                'total_paid_amount' => $totalPaidAmount,
                'recovery_rate' => $recoveryRate,
                'overdue_count' => $overdueCount,
                'overdue_amount' => $overdueAmount,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 4. Laporan Buku Hutang Supplier (Supplier Payables)
     */
    public function getSupplierPayablesReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $supplierId = $filters['supplier_id'] ?? null;

        $query = PurchaseOrder::query()
            ->unpaid()
            ->forBranch($branchId)
            ->betweenDates($dateFrom, $dateTo)
            ->forSupplier($supplierId)
            ->with([
                'supplier',
                'branch',
            ])
            ->latest('date');

        $pos = $query->get();

        $totalPayables = (float) $pos->sum('remaining_amount');
        $totalPoValue = (float) $pos->sum('total');
        $totalPaidToSuppliers = (float) $pos->sum('paid_amount');

        $rows = [];
        foreach ($pos as $po) {
            $rows[] = [
                'id' => $po->id,
                'code' => $po->code,
                'date' => Carbon::parse($po->date)->format('d/m/Y'),
                'supplier_name' => $po->supplier?->name ?? 'Supplier Umum',
                'supplier_phone' => $po->supplier?->phone ?: '-',
                'branch_name' => $po->branch?->name ?? '-',
                'total' => (float) $po->total,
                'paid_amount' => (float) $po->paid_amount,
                'remaining_amount' => (float) $po->remaining_amount,
                'status' => strtoupper($po->status),
            ];
        }

        // Urutkan berdasarkan sisa hutang terbesar
        usort($rows, fn($a, $b) => $b['remaining_amount'] <=> $a['remaining_amount']);

        return [
            'summary' => [
                'total_payables' => $totalPayables,
                'total_pos_count' => count($rows),
                'total_po_value' => $totalPoValue,
                'total_paid_to_suppliers' => $totalPaidToSuppliers,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 5. Laporan Beban & Pengeluaran Toko (Operating Expenses)
     */
    public function getExpensesReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $paymentMethodId = $filters['payment_method_id'] ?? null;

        $query = FinanceExpense::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forPaymentMethod($paymentMethodId)
            ->with([
                'account',
                'paymentMethod',
                'branch',
                'creator',
            ])
            ->latest('date');

        $expenses = $query->get();
        $totalExpenses = (float) $expenses->sum('amount');

        $rows = [];
        foreach ($expenses as $e) {
            $rows[] = [
                'id' => $e->id,
                'date' => Carbon::parse($e->date)->format('d/m/Y'),
                'reference_number' => $e->reference_number ?: "EXP-{$e->id}",
                'branch_name' => $e->branch?->name ?? '-',
                'category_name' => $e->account?->name ?? 'Operasional Toko',
                'payment_method_name' => $e->paymentMethod?->name ?? 'Kas Toko',
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'created_by' => $e->creator?->name ?? '-',
            ];
        }

        // Kategori breakdown
        $categories = [];
        foreach ($expenses->groupBy(fn($e) => $e->account?->name ?? 'Lain-lain') as $name => $items) {
            $sum = (float) $items->sum('amount');
            $pct = $totalExpenses > 0 ? round(($sum / $totalExpenses) * 100, 1) : 0;
            $categories[] = [
                'name' => $name,
                'amount' => $sum,
                'percentage' => $pct,
                'count' => $items->count(),
            ];
        }
        usort($categories, fn($a, $b) => $b['amount'] <=> $a['amount']);

        return [
            'summary' => [
                'total_expenses' => $totalExpenses,
                'total_transactions' => count($rows),
                'top_category_name' => $categories[0]['name'] ?? '-',
                'top_category_amount' => $categories[0]['amount'] ?? 0,
                'top_category_pct' => $categories[0]['percentage'] ?? 0,
            ],
            'categories' => $categories,
            'rows' => $rows,
        ];
    }

    /**
     * 6. Stream Export CSV (UTF-8 BOM)
     */
    public function exportCsv(string $tab, array $filters): StreamedResponse
    {
        $filename = 'Laporan-Keuangan-' . ucfirst($tab) . '-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($tab, $filters) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas instan dengan Microsoft Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            switch ($tab) {
                case 'profit_loss':
                    $data = $this->getProfitLossReport($filters);
                    fputcsv($handle, ['Pos Keuangan Laba Rugi', 'Nominal (Rp)', 'Keterangan']);
                    fputcsv($handle, ['Total Pendapatan Servis & Sparepart', $data['summary']['total_revenue'], 'Omset Penjualan Faktur']);
                    fputcsv($handle, ['Total HPP Modal Sparepart', $data['summary']['total_cogs'], 'Biaya Modal Barang Terjual']);
                    fputcsv($handle, ['Laba Kotor Operasional (Gross Profit)', $data['summary']['gross_profit'], "Margin: {$data['summary']['gross_margin_pct']}%"]);
                    fputcsv($handle, ['Total Beban Operasional Toko', $data['summary']['total_operating_expenses'], 'Biaya Operasional']);
                    fputcsv($handle, ['Laba Bersih Operasional (Net Profit)', $data['summary']['net_profit'], "Margin: {$data['summary']['net_margin_pct']}%"]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Rincian Kategori Biaya Operasional', 'Nominal (Rp)', 'Persentase (%)']);
                    foreach ($data['expense_breakdown'] as $c) {
                        fputcsv($handle, [$c['name'], $c['amount'], $c['percentage'] . '%']);
                    }
                    break;

                case 'receivables':
                    $data = $this->getCustomerReceivablesReport($filters);
                    fputcsv($handle, ['No Invoice', 'Kode Tiket', 'Nama Pelanggan', 'No WhatsApp', 'Cabang', 'Tanggal Invoice', 'Jatuh Tempo', 'Status Tempo', 'Total Tagihan (Rp)', 'Sudah Dibayar (Rp)', 'Sisa Piutang (Rp)']);
                    foreach ($data['rows'] as $r) {
                        fputcsv($handle, [
                            $r['invoice_number'],
                            $r['repair_code'],
                            $r['client_name'],
                            $r['client_whatsapp'],
                            $r['branch_name'],
                            $r['invoice_date'],
                            $r['due_date'],
                            $r['is_overdue'] ? 'Jatuh Tempo (Overdue)' : 'Berjalan',
                            $r['grand_total'],
                            $r['paid_amount'],
                            $r['remaining_amount'],
                        ]);
                    }
                    break;

                case 'payables':
                    $data = $this->getSupplierPayablesReport($filters);
                    fputcsv($handle, ['Kode PO', 'Tanggal PO', 'Nama Supplier', 'No Telepon', 'Cabang', 'Total Pembelian (Rp)', 'Sudah Dibayar (Rp)', 'Sisa Hutang (Rp)', 'Status']);
                    foreach ($data['rows'] as $p) {
                        fputcsv($handle, [
                            $p['code'],
                            $p['date'],
                            $p['supplier_name'],
                            $p['supplier_phone'],
                            $p['branch_name'],
                            $p['total'],
                            $p['paid_amount'],
                            $p['remaining_amount'],
                            $p['status'],
                        ]);
                    }
                    break;

                case 'expenses':
                    $data = $this->getExpensesReport($filters);
                    fputcsv($handle, ['No Referensi', 'Tanggal', 'Cabang', 'Kategori Biaya', 'Metode Pembayaran', 'Keterangan', 'Nominal (Rp)', 'Dicatat Oleh']);
                    foreach ($data['rows'] as $e) {
                        fputcsv($handle, [
                            $e['reference_number'],
                            $e['date'],
                            $e['branch_name'],
                            $e['category_name'],
                            $e['payment_method_name'],
                            $e['description'],
                            $e['amount'],
                            $e['created_by'],
                        ]);
                    }
                    break;

                case 'cashflow':
                default:
                    $data = $this->getCashFlowReport($filters);
                    fputcsv($handle, ['Tanggal', 'Jenis Aliran', 'No Referensi / Transaksi', 'Keterangan Transaksi', 'Cabang', 'Metode Bayar', 'Nominal (Rp)']);
                    foreach ($data['rows'] as $row) {
                        fputcsv($handle, [
                            $row['date'],
                            $row['type_label'],
                            $row['reference'],
                            $row['description'],
                            $row['branch'],
                            $row['method'],
                            ($row['type'] === 'in' ? '+' : '-') . $row['amount'],
                        ]);
                    }
                    break;
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
