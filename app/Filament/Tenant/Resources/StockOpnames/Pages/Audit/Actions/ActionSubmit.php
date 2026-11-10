<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions;

use App\Models\Tenant\StockOpnameItem;
use App\Models\Tenant\StockOpnameItemSerial;
use Illuminate\Support\Facades\DB;

class ActionSubmit
{
    public static function submit($data, $stockOpname, $branchId)
    {
        DB::transaction(function () use ($data, $stockOpname, $branchId) {
            foreach ($data['items'] as $item) {

                $serials = collect($item['serials'] ?? []);

                // Serial hilang
                $lostSerials = $serials
                    ->where('status', 'LOST')
                    ->values()
                    ->map(fn ($s) => [
                        'serial_number' => $s['serial_number'],
                        'note' => $s['note'] ?? null,
                    ]);

                // Serial extra
                $extraSerials = $serials
                    ->where('status', 'EXTRA')
                    ->values()
                    ->map(fn ($s) => [
                        'serial_number' => $s['serial_number'],
                        'note' => $s['note'] ?? null,
                    ]);


                // dd($extraSerials);

                $opnameItem = StockOpnameItem::updateOrCreate(
                    [
                        'stock_opname_id'    => $stockOpname->id,
                        'branch_id'          => $branchId,
                        'product_id'         => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'],
                    ],
                    [
                        'system_qty' => $item['system_qty'],
                        'actual_qty' => $item['actual_qty'],
                        'difference'=> $item['actual_qty'] - $item['system_qty'],

                        'serials_missing' => $lostSerials->toArray(),
                        'serials_found' => $extraSerials->toArray(),
                        'note'      => $item['note'],
                        ]
                    );


                // ============================
                // SYNC SERIAL
                // ============================

                $incomingSerialNumbers = $serials->pluck('serial_number')->toArray();

                // HAPUS serial yang tidak ada di form
                StockOpnameItemSerial::where('stock_opname_item_id', $opnameItem->id)
                    ->whereNotIn('serial_number', $incomingSerialNumbers)
                    ->delete();

                $serials = $item['serials'] ?? [];
                foreach ($serials as $serial) {
                    StockOpnameItemSerial::updateOrCreate(
                        [
                            'stock_opname_item_id' => $opnameItem->id,
                            'product_id'         => $item['product_id'],
                            'product_variant_id' => $item['product_variant_id'],
                            'serial_number' => $serial['serial_number'],
                        ],
                        [
                            'product_serial_id'    => $serial['product_serial_id'] ?? null,
                            'status' => $serial['status'],
                            'note'   => $serial['note'] ?? null,
                        ]
                    );
                }
            }
        });
    }

}
