<?php

Namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Forms;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms;

class ProductVariantForm
{
    public static function schema(Product $product): array
    {
        return [
            Forms\Components\Select::make('product_variant_id')
                ->label('Product Variant')
                ->options(fn ($record) =>
                    // dd($record),
                    $record
                        ? \App\Models\Tenant\ProductVariant::where('product_id', $record->id)->pluck('sku', 'id')
                        : []
                )
                ->disabled(fn ($record) =>
                    // dd($record)
                    $record?->has_variant == 1
                )
                ->extraAttributes(fn ($record) =>
                    // dd($record, $get('product_id')),
                    $record?->has_variant == 0
                        ? ['style' => 'display: none;']
                        : []
                )

                ->afterStateHydrated(function ($state, callable $set, $record, $get) {
                    $product = \App\Models\Tenant\Product::find($get('product_id'));

                    if ($product && $product->has_variant == 0 && $record?->product_variant_id) {
                        $set('product_variant_id', $record->product_variant_id);
                    }
                })
                ->dehydrated()
                ->searchable()
                ->required()
                ->columnSpan(2),

            Forms\Components\TextInput::make('actual_qty')
                ->numeric()
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    // set selisih system_qty dan actual_qty
                    $set(
                        'difference',
                        ($get('actual_qty') ?? 0) - ($get('system_qty') ?? 0)
                    );

                    // set serials repeater
                    $currentSerials = $get('serials') ?? [];
                    $newSerials = [];

                    for ($i = 0; $i < (int) $state; $i++) {
                        $newSerials[$i] = $currentSerials[$i] ?? ['serial_number' => null, 'status' => null, 'note' => null];
                    }

                    $set('serials', $newSerials);

                }),

            Forms\Components\TextInput::make('system_qty')
                ->disabled()
                ->dehydrated(),

            Forms\Components\TextInput::make('difference')
                ->label('Selisih')
                // ->columnSpanFull()
                ->disabled()
                ->dehydrated(false)
                ->live(),

            Forms\Components\Textarea::make('note')
                ->label('Catatan')
                ->columnSpan(1)
                ->rows(2),

            // Repeater untuk serial
            Forms\Components\Repeater::make('serials')
                ->label('Serial Numbers')
                // ->deletable(false)
                ->columnSpanFull()
                ->columns(3)
                ->live(debounce: 300)

                ->deleteAction(function (Action $action) {
                    $action->visible(function (array $arguments, Forms\Components\Repeater $component) {
                        $items = $component->getState();

                        $item = $items[$arguments['item']] ?? null;
                        // dd($item);
                        // ✅ tampilkan tombol hapus hanya jika item belum punya id (item baru)
                        return ! isset($item['product_serial_id']);
                    });
                })
                ->afterStateUpdated(function (callable $set, callable $get) {

                    $serials = $get('serials') ?? [];

                    $actualQty = collect($serials)
                        ->where('status', '!=', 'LOST')
                        ->count();

                    $set('actual_qty', $actualQty);

                    $set(
                        'difference',
                        $actualQty - ($get('system_qty') ?? 0)
                    );
                })
                ->schema([
                    Forms\Components\TextInput::make('serial_number')
                        ->label('Serial')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'OK' => 'OK',
                            'LOST' => 'Lost',
                            'EXTRA' => 'Extra',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('note')
                        ->label('Catatan')
                        ->rows(2),
                ])
                ->minItems(0)
                ->visible(function (callable $get) {
                    return Product::find($get('product_id'))?->has_sn;
                }),
        ];
    }
}
