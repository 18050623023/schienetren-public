<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Actions;

use App\Models\Tenant\StockTransfer;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class StockTransferActionStatus
{
    public static function shipAction(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            if ($transfer->status !== 'draft') {
                abort(403, 'Hanya dokumen draft yang dapat dikirim.');
            }

            $transfer->load('items.itemSerials.productSerial');

            foreach ($transfer->items as $item) {
                // 1. Catat Stock Movement (Keluar dari Cabang Asal)
                StockMovement::create([
                    'branch_id' => $transfer->from_branch_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'direction' => 'out',
                    'quantity' => $item->qty_sent,
                    'note' => 'Pengiriman Mutasi #' . $transfer->code,
                    'source_type' => 'stock_transfer_ship',
                    'source_id' => $transfer->id,
                ]);

                // 2. Kurangi stok di Cabang Asal
                $stock = Stock::firstOrNew([
                    'branch_id' => $transfer->from_branch_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                ], [
                    'stock' => 0,
                ]);

                if (!$stock->exists) {
                    $stock->stock = 0;
                }

                $stock->stock -= $item->qty_sent;
                $stock->save();

                // 3. Ubah status Serial Number menjadi 'transfered' (in transit)
                if ($item->itemSerials->isNotEmpty()) {
                    foreach ($item->itemSerials as $serialRecord) {
                        if ($serialRecord->productSerial) {
                            $serialRecord->productSerial->update([
                                'status' => 'transfered',
                            ]);
                        }
                    }
                }
            }

            // Update status mutasi
            $transfer->update([
                'status' => 'in_transit',
                'shipped_by' => Auth::id(),
                'shipped_at' => now(),
            ]);
        });

        Notification::make()
            ->title('Barang berhasil dikirim dan stok asal telah dikurangi.')
            ->success()
            ->send();
    }

    public static function receiveAction(StockTransfer $transfer, array $receivedItems): void
    {
        DB::transaction(function () use ($transfer, $receivedItems) {
            if (!in_array($transfer->status, ['in_transit', 'partial_received'])) {
                abort(403, 'Mutasi tidak sedang dalam pengiriman atau penerimaan sebagian.');
            }

            $transfer->load('items.itemSerials.productSerial');
            $allFullReceived = true;

            foreach ($transfer->items as $item) {
                // Cari data penerimaan untuk item ini
                $receivedData = collect($receivedItems)->firstWhere('item_id', $item->id);
                $qtyReceived = $receivedData['qty_received'] ?? 0;
                $receivedSerialIds = collect($receivedData['received_serials'] ?? [])->map(fn($val) => (int)$val)->toArray();

                $newTotalReceived = $item->qty_received + $qtyReceived;

                if ($qtyReceived > 0) {
                    // 1. Catat Stock Movement (Masuk ke Cabang Tujuan)
                    StockMovement::create([
                        'branch_id' => $transfer->to_branch_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'direction' => 'in',
                        'quantity' => $qtyReceived,
                        'note' => 'Penerimaan Mutasi #' . $transfer->code,
                        'source_type' => 'stock_transfer_receive',
                        'source_id' => $transfer->id,
                    ]);

                    // 2. Tambah stok di Cabang Tujuan
                    $stock = Stock::firstOrNew([
                        'branch_id' => $transfer->to_branch_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                    ], [
                        'stock' => 0,
                    ]);

                    if (!$stock->exists) {
                        $stock->stock = 0;
                    }

                    $stock->stock += $qtyReceived;
                    $stock->save();
                }

                // Update Serial Numbers (Jika Ada)
                if ((bool) $item->product->has_sn && $item->itemSerials->isNotEmpty()) {
                    foreach ($item->itemSerials as $serialRecord) {
                        $productSerial = $serialRecord->productSerial;
                        if ($productSerial && $productSerial->status === 'transfered') {
                            if (in_array($productSerial->id, $receivedSerialIds)) {
                                // Serial diterima
                                $productSerial->update([
                                    'branch_id' => $transfer->to_branch_id,
                                    'status' => 'available',
                                ]);
                            }
                            // Kita tidak langsung set lost_in_transit karena mungkin datang di kloter berikutnya (partial received)
                        }
                    }
                }

                // Temporary override booted observer logic in StockTransferItem
                StockTransferItem::withoutEvents(function () use ($item, $newTotalReceived) {
                    $item->update([
                        'qty_received' => $newTotalReceived,
                    ]);
                });

                if ($newTotalReceived < $item->qty_sent) {
                    $allFullReceived = false;
                }
            }

            // Update status mutasi
            $newStatus = $allFullReceived ? 'received' : 'partial_received';
            
            StockTransfer::withoutEvents(function () use ($transfer, $newStatus) {
                $transfer->update([
                    'status' => $newStatus,
                    'received_by' => Auth::id(),
                    'received_at' => now(),
                ]);
            });
        });

        Notification::make()
            ->title('Penerimaan berhasil diselesaikan.')
            ->success()
            ->send();
    }
}
