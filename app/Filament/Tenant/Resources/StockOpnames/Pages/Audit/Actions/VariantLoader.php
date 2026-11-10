<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions;


class VariantLoader
{
    public static function load($record, $branchId)
    {
        if (! $branchId) {
            return [];
        }

        // $back = $product->variants()
        return $record->variants()
        ->with(['productSerial' => function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->where('status', 'available')
                ->orderBy('serial_number');
        }])
        ->get()
        ->map(function ($variant) {
            // dd($variant);
            return [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'variant_name' => $variant->name,
                'system_qty' => $variant->stocks->where('branch_id', session('current_branch_id'))->sum('stock'),
                'serials' => $variant->productSerial/* ->where('branch_id', session('current_branch_id')) */->map(fn ($serial) => [
                    'product_serial_id' => $serial->id,
                    'serial_number' => $serial->serial_number,
                    'status' => $serial->status,
                    'note' => null,
                ])->toArray(),
            ];
        })
        ->toArray();
    }

    public static function edit($stockOpname, $record, $branchId)
    {
        if (! $branchId) {
            return [];
        }
        // dd($stockOpname->items()->where('product_id', $record->id)->get());
        // $back = $product->variants()
        return $stockOpname->items()
        ->where('product_id', $record->id)
        ->where('branch_id', $branchId)
        ->with(['serials'])
        ->get()
        ->map(function ($item) {
            // dd($variant);
            return [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'variant_name' => $item->name,
                'system_qty' => $item->system_qty,
                'actual_qty' => $item->actual_qty,
                'difference' => $item->difference,
                'serials' => $item->serials
                    ->map(fn ($serial) => [
                        'product_serial_id' => $serial->product_serial_id,
                        'serial_number' => $serial->serial_number,
                        'status' => $serial->status,
                        'note' => $serial->note,
                    ])->toArray(),
            ];
        })
        ->toArray();
    }

}
