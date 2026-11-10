<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Forms;

use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Forms\ProductVariantForm;
use Filament\Forms\Components\TextInput;
use App\Models\Tenant\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;

class AuditProductForm
{
    public static function schema(Product $product): array
    {
        // dd($product);
        return [
            // hidden
            Hidden::make('action')
                ->default(fn ($get) => $get('action')),
            Repeater::make('items')
                ->label(fn () =>  $product?->has_variant == 1 ? 'Product Variants' : 'Product')
                ->required()
                ->deletable(false)
                ->collapsible(fn () => $product?->has_variant == 1  )
                ->addable(false)
                // ->default(fn ($record) =>self::loadSystemSerials($product)  )
                ->itemLabel(fn (array $state): string =>
                //    dd($state)
                    $state['variant_name'] ?? ''
                )
                ->schema(ProductVariantForm::schema($product))
                ->columns(2),
        ];
    }



    // protected static function loadSystemSerials(Product $product): array
    // {
    //     $branchId = session('current_branch_id');

    //     if (! $branchId) {
    //         return [];
    //     }

    //     // $back = $product->variants()
    //     return $product->variants()
    //     ->with(['product_serial' => function ($query) use ($branchId) {
    //         $query->where('branch_id', $branchId)
    //               ->where('status', 'available')
    //               ->orderBy('serial_number');
    //     }])
    //     ->get()
    //     ->map(function ($variant) {
    //         // dd($variant);
    //         return [
    //             'product_id' => $variant->product_id,
    //             'product_variant_id' => $variant->id,
    //             'variant_name' => $variant->name,
    //             'qty' => $variant->stocks->where('branch_id', session('current_branch_id'))->sum('stock'),
    //             'serials' => $variant->product_serial/* ->where('branch_id', session('current_branch_id')) */->map(fn ($serial) => [
    //                 'product_serial_id' => $serial->id,
    //                 'serial_number' => $serial->serial_number,
    //                 'status' => $serial->status,
    //                 'note' => null,
    //             ])->toArray(),
    //         ];
    //     })
    //     ->toArray();
    //     // dd($back);
    // }
}
