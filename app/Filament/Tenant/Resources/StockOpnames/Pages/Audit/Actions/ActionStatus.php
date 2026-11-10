<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions;

use App\Enums\TenantFeature;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockOpnameItem;
use App\Services\Stock\StockMovementService;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class ActionStatus
{
    public static function initialStockPost(StockOpname $opname): void
    {
        if ($opname->status !== 'draft') {
            abort(403, 'Stock opname sudah diproses, tidak bisa di-post.');
        }

        if ($opname->type === 'regular') {
            abort_unless(tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME), 403, 'Fitur Stock Opname tidak aktif pada paket langganan Anda.');
        }

        DB::transaction(function () use ($opname) {
            // dd($opname->items) ;
            foreach ($opname->items as $item) {

                match ($opname->type) {
                    'initial' => static::postInitialItem($opname, $item),
                    'regular'   => static::postAuditItem($opname, $item),
                    // default   => throw new \Exception('Tipe opname tidak dikenal'),
                    default => abort(403, 'Tipe opname tidak dikenal'),
                };
            }

            $opname->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Filament::auth()->id(),
            ]);
        });

        Notification::make()
            ->title(fn() => ($opname->type === 'initial' ? 'Initial Stock dipost,' : 'Audit Stock dipost,') . ' Stock Opname dikunci')
            ->success()
            ->send();
    }

    protected static function postInitialItem(StockOpname $opname, $item): void
    {
        $stock = Stock::firstOrCreate(
            [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'branch_id' => $item->branch_id,
            ],
            [
                'stock' => 0,
            ]
        );
        
        $oldStockQty = $stock->stock;
        
        $stock->update([
            'stock' => $item->actual_qty,
        ]);
        
        $difference = $item->actual_qty - $oldStockQty;
        
        $serialIds = [];
        if ($item->serials) {
            foreach ($item->serials as $serial) {
                $productSerial = ProductSerial::updateOrCreate(
                    [
                        'branch_id' => $item->branch_id,
                        'product_variant_id' => $item->product_variant_id,
                        'serial_number' => $serial->serial_number,
                    ],
                    [
                        'status' => 'available',
                    ]
                );
                
                if ($productSerial->status !== 'available') {
                    $productSerial->update([
                        'status' => 'available',
                    ]);
                }
                
                $serialIds[] = $productSerial->id;
            }
        }

        if ($difference !== 0) {
            StockMovementService::create(
                source: $opname,
                attributes: [
                    'branch_id' => $opname->branch_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'direction' => $difference > 0 ? 'in' : 'out',
                    'quantity' => abs($difference),
                ],
                serialIds: $serialIds,
            );
        }
    }

    protected static function postAuditItem(StockOpname $opname, $item): void
    {
        $difference = $item->actual_qty - $item->system_qty;
                // $opnameSerials = $item->serials->pluck('product_serial_id')->filter()->toArray();
        $opnameSerials = $item->serials->where('status', '!=', 'LOST')->pluck('product_serial_id')->filter()->toArray();

        // MERUBAH STATUS SERIAL YANG TIDAK ADA DI OPNAME
        // Missing serial
        ProductSerial::where('branch_id', $opname->branch_id)
            ->where('product_variant_id', $item->product_variant_id)
            ->whereNotIn('id', $opnameSerials)
            ->update(['status' => 'lost']);

        // dd($item->serials_found);

        $serialIds = [];

        // loop serial
        foreach ($item->serials as $serial) {
            $targetStatus = ($serial->status === 'LOST') ? 'lost' : 'available';

            // Update product serial yang ada di stock opname
            // Extra serial
            $productSerial = ProductSerial::updateOrCreate(
                [
                    'branch_id' => $item->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                    'serial_number' => $serial->serial_number,
                ],
                [
                    // 'status' => 'available',
                    'status' => $targetStatus,
                ]
            );

            // if ($productSerial->status !== 'available') {
            if ($productSerial->status !== $targetStatus) {
                $productSerial->update([
                    // 'status' => 'available',
                    'status' => $targetStatus,
                ]);
            }

            if ($targetStatus === 'available') {
                $serialIds[] = $productSerial->id;
            }
        }

            // if($difference !== 0){

            //     $movement->productSerials()->syncWithoutDetaching([
            //         $productSerial->id
            //     ]);
            // }

        //Buat movement jika terdapat perubahan jumlah stock
        $movement = null;

        if($difference !== 0){
            $movement = StockMovementService::create(
                source: $opname,
                attributes: [
                    'branch_id' => $opname->branch_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'direction' => $difference > 0 ? 'in' : 'out',
                    'quantity' => abs($difference),
                    // 'note' => $item->serials_missing.' '.$item->serials_found,
                ],
                serialIds: $serialIds,
            );
            // $movement = $opname->stockMovements()->create([
            //     'branch_id' => $opname ->branch_id,
            //     'product_id' => $item->product_id,
            //     'product_variant_id' => $item->product_variant_id,
            //     'direction' => $difference > 0 ? 'in' : 'out',
            //     'quantity' => abs($difference),
            //     // 'note' => $item->serials_missing.' '.$item->serials_found,
            //     // 'source_type' => 'audit',
            //     // 'source_id' => $opname->id,
            // ]);
        }

        // if ($movement && $serialIds) {
        //     $movement->productSerials()
        //         ->syncWithoutDetaching($serialIds);
        // }


        // update stocks table menyesuaikan dengan opname
        $stock = Stock::firstOrCreate(
            [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'branch_id' => $item->branch_id,
            ],
            [
                'stock' => 0,
            ]
        );

        $stock->update([
            'stock' => $item->actual_qty,
        ]);


        // $stock = Stock::where('product_id', $item->product_id)
        //             ->where('product_variant_id', $item->product_variant_id)
        //             ->where('branch_id', $item->branch_id)
        //             ->first();
        // if ($stock) {
        //     $stock->stock = $item['actual_qty'];
        //     $stock->save();
        // } else {
        //     Stock::create([
        //         'product_id' => $item['product_id'],
        //         'product_variant_id' => $item['product_variant_id'] ?? null,
        //         'branch_id' => $item['branch_id'],
        //         'stock' => $item['actual_qty'],
        //     ]);
        // }

    }

    public static function initialStockCancel(StockOpname $record): string
    {
        // dd($record);

        return DB::transaction(function () use ($record) {

            if ($record->status !== 'draft') {
                abort(403, 'Stock opname sudah diproses, tidak bisa dibatalkan.');
            }

            if ($record->type !== 'initial') {
                abort(403, 'Bukan initial stock.');
            }

            $hasUsedSerial = ProductSerial::where('branch_id', $record->branch_id)
                ->whereHas('stockMovements')
                ->exists();

            if ($hasUsedSerial) {
                abort(403, 'Stock opname ini sudah digunakan, tidak bisa dibatalkan.');
            }

            // 🔥 RESET GUDANG
            ProductSerial::where('branch_id', $record->branch_id)->delete();
            Stock::where('branch_id', $record->branch_id)->delete();
            StockOpnameItem::where('stock_opname_id', $record->id)->delete();
            $record->delete();

            return true;
        });
    }

    public static function stockAuditCancel(StockOpname $record): string
    {
        return DB::transaction(function () use ($record) {
            if ($record->status !== 'draft') {
                abort(403, 'Stock opname sudah diproses, tidak bisa dibatalkan.');
            }

            if ($record->type !== 'regular') {
                abort(403, 'Bukan audit stock.');
            }

            abort_unless(tenant()->hasFeatureCode(TenantFeature::STOCK_OPNAME), 403, 'Fitur Stock Opname tidak aktif pada paket langganan Anda.');

            $record->update(['status' => 'canceled']   );

            return true;
        });
    }
}
