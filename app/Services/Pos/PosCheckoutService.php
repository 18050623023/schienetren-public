<?php

namespace App\Services\Pos;

use App\Enums\TenantFeature;
use App\Models\Tenant\Branch;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Models\Tenant\SaleItemSerial;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class PosCheckoutService
{
    /**
     * Memproses checkout transaksi POS dengan pessimistic locking pada stok.
     *
     * @param array $data
     * @return Sale
     * @throws Exception
     */
    public function checkout(array $data): Sale
    {
        if (!tenant()->hasFeatureCode(TenantFeature::POS)) {
            throw new Exception('Fitur Point of Sale (POS) tidak aktif.');
        }

        return DB::transaction(function () use ($data) {
            $branchId = (int) $data['branch_id'];
            $branch = Branch::findOrFail($branchId);
            $cashierId = (int) ($data['cashier_id'] ?? auth('tenant')->id());

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new Exception('Keranjang belanja masih kosong.');
            }

            // 1. Generate nomor invoice unik dengan locking
            $invoiceNumber = Sale::generateInvoiceNumber($branch, true);

            // 2. Pra-kalkulasi subtotal & validasi stok dengan lockForUpdate
            $calculatedSubtotal = 0;
            $preparedItems = [];

            // Akumulasi total permintaan kuantitas per varian untuk validasi stok gabungan
            $requestedQtyPerVariant = [];
            foreach ($itemsData as $row) {
                $vId = (int) $row['product_variant_id'];
                $q = max(1, (int) ($row['qty'] ?? 1));
                $requestedQtyPerVariant[$vId] = ($requestedQtyPerVariant[$vId] ?? 0) + $q;
            }

            foreach ($requestedQtyPerVariant as $vId => $totalReqQty) {
                $stock = Stock::where('branch_id', $branchId)
                    ->where('product_variant_id', $vId)
                    ->lockForUpdate()
                    ->first();

                $currentStock = $stock ? (int) $stock->stock : 0;
                if ($currentStock < $totalReqQty) {
                    $variant = ProductVariant::with('product')->find($vId);
                    $prodName = $variant?->product?->name ?? 'Produk';
                    $varName = $variant?->name ? " ({$variant->name})" : '';
                    throw new Exception("Stok untuk \"{$prodName}{$varName}\" tidak mencukupi. Tersedia: {$currentStock}, diminta: {$totalReqQty}.");
                }
            }

            foreach ($itemsData as $row) {
                $variantId = (int) $row['product_variant_id'];
                $qty = max(1, (int) ($row['qty'] ?? 1));

                $variant = ProductVariant::with('product')->findOrFail($variantId);
                $productId = $variant->product_id;

                $unitPrice = isset($row['unit_price']) ? (float) $row['unit_price'] : (float) $variant->sale_price;
                $discountAmount = max(0, (float) ($row['discount_amount'] ?? 0));
                $itemSubtotal = max(0, ($unitPrice * $qty) - $discountAmount);
                $costPrice = (float) ($variant->purchase_price ?? 0);

                $calculatedSubtotal += $itemSubtotal;

                $preparedItems[] = [
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'qty' => $qty,
                    'cost_price' => $costPrice,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $itemSubtotal,
                    'warranty_days' => (int) ($row['warranty_days'] ?? 0),
                    'serials' => $row['serials'] ?? [],
                    'stock_model' => $stock,
                ];
            }

            // 3. Kalkulasi Diskon Global, Pajak, dan Grand Total
            $discountType = $data['discount_type'] ?? null;
            $discountValue = (float) ($data['discount_value'] ?? 0);
            $discountAmount = 0;

            if ($discountType === 'percentage') {
                $discountAmount = ($calculatedSubtotal * ($discountValue / 100));
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($calculatedSubtotal, $discountValue);
            }

            $taxAmount = max(0, (float) ($data['tax_amount'] ?? 0));
            $grandTotal = max(0, ($calculatedSubtotal - $discountAmount) + $taxAmount);

            $paidAmount = (float) ($data['paid_amount'] ?? 0);
            if ($paidAmount < $grandTotal) {
                throw new Exception("Nominal pembayaran kurang. Total: Rp " . number_format($grandTotal, 0, ',', '.') . ", dibayar: Rp " . number_format($paidAmount, 0, ',', '.'));
            }

            $changeAmount = max(0, $paidAmount - $grandTotal);

            // 4. Buat Rekord Sale
            $sale = Sale::create([
                'branch_id' => $branchId,
                'client_id' => $data['client_id'] ?? null,
                'cashier_id' => $cashierId,
                'cash_shift_id' => $data['cash_shift_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'date' => now(),
                'status' => 'completed',
                'subtotal' => $calculatedSubtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // 5. Potong Stok, Catat Mutasi, dan Simpan Items
            foreach ($preparedItems as $prep) {
                Stock::where('branch_id', $branchId)
                    ->where('product_variant_id', $prep['product_variant_id'])
                    ->decrement('stock', $prep['qty']);

                $saleItem = $sale->items()->create([
                    'product_id' => $prep['product_id'],
                    'product_variant_id' => $prep['product_variant_id'],
                    'qty' => $prep['qty'],
                    'cost_price' => $prep['cost_price'],
                    'unit_price' => $prep['unit_price'],
                    'discount_amount' => $prep['discount_amount'],
                    'subtotal' => $prep['subtotal'],
                    'warranty_days' => $prep['warranty_days'],
                ]);

                // Buat mutasi stok keluar
                $movement = StockMovement::create([
                    'branch_id' => $branchId,
                    'product_id' => $prep['product_id'],
                    'product_variant_id' => $prep['product_variant_id'],
                    'direction' => 'out',
                    'quantity' => $prep['qty'],
                    'note' => 'Penjualan POS #' . $sale->invoice_number,
                    'source_type' => SaleItem::class,
                    'source_id' => $saleItem->id,
                ]);

                // Tangani nomor seri jika disertakan
                $serialIds = [];
                if (!empty($prep['serials'])) {
                    foreach ($prep['serials'] as $serialInput) {
                        $serial = null;
                        if (is_numeric($serialInput)) {
                            $serial = ProductSerial::find($serialInput);
                        } else {
                            $serial = ProductSerial::where('branch_id', $branchId)
                                ->where('product_variant_id', $prep['product_variant_id'])
                                ->where('serial_number', $serialInput)
                                ->first();
                        }

                        if ($serial) {
                            $serialIds[] = $serial->id;
                            $serial->update(['status' => 'sold']);

                            SaleItemSerial::create([
                                'sale_item_id' => $saleItem->id,
                                'product_serial_id' => $serial->id,
                                'serial_number' => $serial->serial_number,
                            ]);
                        }
                    }

                    if (!empty($serialIds)) {
                        $movement->productSerials()->syncWithoutDetaching($serialIds);
                    }
                }
            }

            return $sale->load(['items.productVariant.product', 'items.serials', 'client', 'paymentMethod', 'cashier']);
        });
    }

    /**
     * Membatalkan transaksi POS dan mengembalikan stok secara atomik.
     *
     * @param Sale $sale
     * @param string $reason
     * @return Sale
     * @throws Exception
     */
    public function voidSale(Sale $sale, string $reason): Sale
    {
        return DB::transaction(function () use ($sale, $reason) {
            if ($sale->status !== 'completed') {
                throw new Exception("Hanya transaksi dengan status 'completed' yang dapat dibatalkan.");
            }

            $sale->load(['items.serials.productSerial']);

            foreach ($sale->items as $item) {
                // Kunci dan kembalikan kuantitas stok cabang
                $stock = Stock::where('branch_id', $sale->branch_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->stock += $item->qty;
                    $stock->save();
                }

                // Catat mutasi stok masuk kembali (reversal)
                $movement = StockMovement::create([
                    'branch_id' => $sale->branch_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'direction' => 'in',
                    'quantity' => $item->qty,
                    'note' => 'Pembatalan (Void) POS #' . $sale->invoice_number . ' - ' . $reason,
                    'source_type' => SaleItem::class,
                    'source_id' => $item->id,
                ]);

                // Kembalikan status serial number menjadi available
                if ($item->serials->isNotEmpty()) {
                    $serialIds = [];
                    foreach ($item->serials as $itemSerial) {
                        if ($itemSerial->productSerial) {
                            $itemSerial->productSerial->update(['status' => 'available']);
                            $serialIds[] = $itemSerial->productSerial->id;
                        }
                    }
                    if (!empty($serialIds)) {
                        $movement->productSerials()->syncWithoutDetaching($serialIds);
                    }
                }
            }

            $sale->update([
                'status' => 'void',
                'notes' => trim($sale->notes . " [VOID: {$reason}]"),
            ]);

            return $sale;
        });
    }
}
