<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Schemas;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Notifications\Notification;

class SectionItemFormGoodsReceipt
{
    public static function schema(): array
    {
        return [

            Repeater::make('items')
                ->label('Products')
                ->required()
                ->live()
                // ->relationship('items')
                ->collapsible()
                // ->defaultItems(0)
                ->reorderableWithDragAndDrop(false)
                ->addable(false)
                ->schema([
                    // form hidden purchase oreder id
                    Forms\Components\hidden::make('purchase_order_item_id')
                        ->dehydrated()
                        ,

                    Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->options(Product::pluck('name', 'id')->toArray())
                        ->disabled()
                        ->searchable()
                        ->live(debounce:500)
                        ->required(),

                    Forms\Components\Select::make('product_variant_id')
                        ->options(function (callable $get) {
                            if (!$get('product_id')) return [];
                            return ProductVariant::where('product_id', $get('product_id'))
                                ->get()
                                ->mapWithKeys(function ($variant) {
                                    return [
                                        $variant->id => filled($variant->name)
                                            ? $variant->name
                                            : $variant->sku
                                    ];
                                });
                        })
                        ->disabled()
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('qty_received')
                        ->numeric()
                        ->minValue(1)
                        // ->maxValue(2)
                        // ->rule('max:2')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            // dd( $get());
                            if ($state > $get('qty')) {
                                $set('qty_received', $get('qty'));

                                Notification::make()
                                    ->title('Tidak bole melebihi maksimal QTY Purchase Order')
                                    ->success()
                                    ->send();
                            }
                        })
                        // ->live(debounce:500)
                        ->required()
                        ,
                ])

                ->table([
                    TableColumn::make('Product'),
                    TableColumn::make('Variant'),
                    TableColumn::make('Received Quantity')
                        ->width('150px'),
                    // TableColumn::make('Price')
                    //     ->width('180px'),
                    // TableColumn::make('Subtotal')
                    //     ->width('180px'),
                ])
                ->required(),
        ];
    }
}

?>
