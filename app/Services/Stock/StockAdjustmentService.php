<?php

namespace App\Services\Stock;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class StockAdjustmentService
{
    /**
     * Lakukan penyesuaian stok manual untuk sebuah item produk di cabang tertentu.
     *
     * @param int $branchId
     * @param int $productId
     * @param int|null $variantId
     * @param string $direction ('in' | 'out')
     * @param int $quantity
     * @param string $reason
     * @param string|null $note
     * @param array $selectedSerialIds (ID ProductSerial untuk direction 'out')
     * @param array $newSerialNumbers (String serial_number baru untuk direction 'in')
     * @return Stock
     * @throws Exception
     */
    public function adjust(
        int $branchId,
        int $productId,
        ?int $variantId,
        string $direction,
        int $quantity,
        string $reason,
        ?string $note = null,
        array $selectedSerialIds = [],
        array $newSerialNumbers = []
    ): Stock {
        if ($quantity <= 0) {
            throw new Exception('Kuantitas penyesuaian harus lebih dari 0.');
        }

        if (!in_array($direction, ['in', 'out'])) {
            throw new Exception('Tipe penyesuaian tidak valid (harus in atau out).');
        }

        return DB::transaction(function () use (
            $branchId,
            $productId,
            $variantId,
            $direction,
            $quantity,
            $reason,
            $note,
            $selectedSerialIds,
            $newSerialNumbers
        ) {
            $product = Product::findOrFail($productId);

            // Jika variantId kosong, ambil varian pertama produk
            if (!$variantId) {
                $variantId = $product->variants()->first()?->id;
            }

            // 1. Ambil atau buat record Stock dengan lock
            $stock = Stock::firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                ],
                [
                    'stock' => 0,
                ]
            );

            // Lock row untuk mencegah race condition
            $stock = Stock::where('id', $stock->id)->lockForUpdate()->first();

            // 2. Validasi stok untuk direction 'out'
            if ($direction === 'out' && $stock->stock < $quantity) {
                throw new Exception("Stok tidak mencukupi untuk pengurangan. Stok saat ini: {$stock->stock}, diminta: {$quantity}.");
            }

            // 3. Validasi Serial Number jika produk memiliki SN
            $movementSerialIds = [];

            if ($product->has_sn) {
                if ($direction === 'out') {
                    $selectedCount = count($selectedSerialIds);
                    if ($selectedCount !== $quantity) {
                        throw new Exception("Jumlah nomor seri yang dipilih ({$selectedCount}) harus sama dengan kuantitas pengurangan ({$quantity}).");
                    }

                    $serials = ProductSerial::whereIn('id', $selectedSerialIds)
                        ->where('branch_id', $branchId)
                        ->where('status', 'available')
                        ->get();

                    if ($serials->count() !== $quantity) {
                        throw new Exception("Salah satu atau lebih nomor seri yang dipilih tidak valid atau tidak berstatus available di cabang ini.");
                    }

                    $targetStatus = match ($reason) {
                        'Barang Rusak / Pecah' => 'damaged',
                        'Barang Hilang / Selisih' => 'missing',
                        default => 'damaged',
                    };

                    foreach ($serials as $serial) {
                        $serial->update(['status' => $targetStatus]);
                        $movementSerialIds[] = $serial->id;
                    }
                } elseif ($direction === 'in') {
                    $cleanSerials = array_values(array_filter(array_map('trim', $newSerialNumbers)));
                    $newCount = count($cleanSerials);

                    if ($newCount !== $quantity) {
                        throw new Exception("Jumlah nomor seri yang diinput ({$newCount}) harus sama dengan kuantitas penambahan ({$quantity}).");
                    }

                    foreach ($cleanSerials as $sn) {
                        $existing = ProductSerial::where('serial_number', $sn)
                            ->where('product_variant_id', $variantId)
                            ->first();

                        if ($existing && $existing->status === 'available') {
                            throw new Exception("Nomor seri '{$sn}' sudah terdaftar dan berstatus available di sistem.");
                        }

                        if ($existing) {
                            $existing->update([
                                'branch_id' => $branchId,
                                'status' => 'available',
                            ]);
                            $movementSerialIds[] = $existing->id;
                        } else {
                            $newSerial = ProductSerial::create([
                                'branch_id' => $branchId,
                                'product_variant_id' => $variantId,
                                'serial_number' => $sn,
                                'status' => 'available',
                            ]);
                            $movementSerialIds[] = $newSerial->id;
                        }
                    }
                }
            }

            // 4. Update Saldo Stok Fisik
            if ($direction === 'in') {
                $stock->stock += $quantity;
            } else {
                $stock->stock -= $quantity;
            }
            $stock->save();

            // 5. Catat Histori StockMovement (Kartu Stok)
            $formattedNote = '[Penyesuaian Manual] ' . $reason;
            if (!empty($note)) {
                $formattedNote .= ': ' . trim($note);
            }

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'direction' => $direction,
                'quantity' => $quantity,
                'note' => $formattedNote,
                'source_type' => 'manual_adjustment',
                'source_id' => Auth::id(),
            ]);

            // Sync serial ke pivot table jika ada
            if (!empty($movementSerialIds)) {
                $movement->productSerials()->syncWithoutDetaching($movementSerialIds);
            }

            return $stock;
        });
    }
}
