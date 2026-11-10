<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Schemas;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Resources\Pages\ViewRecord;

class SectionItemForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Select::make('product_search')
                ->label('Cari Produk')
                ->searchable()
                ->live()
                ->dehydrated(false)
                ->getOptionLabelUsing(fn ($value) =>
                    optional(ProductVariant::with('product.brand')->find($value),
                        fn ($variant) => $variant->product->brand->name . ' ' .
                                        $variant->product->name . ' - ' .
                                        ($variant->name ?: $variant->sku)
                    )
                )
                ->getSearchResultsUsing(function (string $search) {
                    return ProductVariant::query()
                        ->select('product_variants.*')
                        ->join('products', 'products.id', '=', 'product_variants.product_id')
                        ->join('brands', 'brands.id', '=', 'products.brand_id')
                        ->where(function ($query) use ($search) {
                            $query->where('products.name', 'like', "%{$search}%")
                                ->orWhere('products.code', 'like', "%{$search}%")
                                ->orWhere('product_variants.name', 'like', "%{$search}%")
                                ->orWhere('product_variants.sku', 'like', "%{$search}%")
                                ->orWhere('brands.name', 'like', "%{$search}%");
                        })
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(function ($variant) {
                            // dd($variant);
                            $return = [
                                $variant->id =>
                                    $variant->product->brand->name .' '. $variant->product->name . ' - ' . ($variant->name ?: $variant->sku)
                            ];
                            return $return;
                            // dd($return);
                        });
                })
                ->afterStateUpdated(function ($state, callable $set, callable $get) {

                    if (!$state) return;

                    $variant = ProductVariant::with('product')->find($state);
                    if (!$variant) return;

                    $items = $get('items') ?? [];

                    $found = false;

                    foreach ($items as &$item) {

                        if (($item['product_variant_id'] ?? null) == $variant->id) {
                            $item['qty'] += 1;
                            $item['subtotal'] = $item['qty'] * $item['price'];
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $items[] = [
                            'product_id' => $variant->product_id,
                            'product_variant_id' => $variant->id,
                            // 'variant_name' => $variant->name,
                            'qty' => 1,
                            'price' => $variant->purchase_price,
                            'subtotal' => $variant->purchase_price,
                        ];
                    }

                    $set('total', array_sum(array_map(fn ($item) =>
                        ($item['qty'] ?? 0) * ($item['price'] ?? 0),
                    $items)));

                    $set('items', $items);
                    $set('product_search', null);
                    PurchaseOrderForm::hitungSisa($get, $set);
                    // dd($total);
                }),


            Repeater::make('items')
                ->label('Products')
                ->required()
                ->live()
                ->relationship('items')
                ->collapsible()
                ->defaultItems(0)
                ->reorderableWithDragAndDrop(false)
                ->addable(false)
                ->schema(function ($livewire) {

                    $isView = $livewire instanceof \Filament\Resources\Pages\ViewRecord;

                    return array_values(array_filter([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(Product::pluck('name', 'id')->toArray())
                            ->disabled()->dehydrated()
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
                            ->disabled()->dehydrated()
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('qty')
                            ->numeric()
                            ->minValue(1)
                            ->live(debounce:500)
                            ->required()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::updateRepeater($get, $set);
                            }),

                        $isView
                            ? Forms\Components\TextInput::make('qty_pending')
                                ->disabled()
                                ->numeric()
                            : null,

                        Forms\Components\TextInput::make('price')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->live(debounce:500)
                            ->afterStateUpdated(function ( callable $get, callable $set) {
                                self::updateRepeater($get, $set);
                            })
                            ,
                        Forms\Components\TextInput::make('subtotal')
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ,
                    ]));
                })

                ->table(fn (string $operation) => array_filter([
                    TableColumn::make('Product'),
                    TableColumn::make('Variant'),
                    TableColumn::make('Quantity')
                        ->width('90px'),
                    $operation === 'view'
                        ? TableColumn::make('Pending')->width('90px')
                        : null,
                    TableColumn::make('Price')
                        ->width('180px'),
                    TableColumn::make('Subtotal')
                        ->width('180px'),
                ]))
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    // <-- INI KUNCI AGAR DELETE IKUT KEHITUNG
                    $total = collect($state)->sum('subtotal');
                    $discount = $get('discount') ?? 0;

                    $set('total', $total);
                    $set('remaining_amount', $total - $discount);
                    PurchaseOrderForm::hitungSisa($get, $set);
                })
                // ->afterStateUpdated(function ($state, callable $set, callable $get) {

                //     $total = array_sum(array_column($state ?? [], 'subtotal'));
                //     $set('total', $total);
                // })
                ->required(),
        ];
    }

    public static function updateRepeater($get, $set)
    {

        $items = $get('../../items') ?? [];

        // ambil nilai terbaru dari field saat ini
        $qty = $get('qty') ?? 0;
        $price = $get('price') ?? 0;

        $subtotal = $qty * $price;

        // set subtotal field ini
        $set('subtotal', $subtotal);

        // 🔥 recompute total dari source of truth (fresh state)
        $total = array_sum(array_map(function ($item) {
            return ($item['qty'] ?? 0) * ($item['price'] ?? 0);
        }, $items));

        $set('../../total', $total);

        // hitung sisa
        $total = (float) ($get('../../total') ?? 0);
        $discount = (float) ($get('../../discount') ?? 0);
        $paid_amount = (float) ($get('../../paid_amount') ?? 0);

        $netTotal = max(0, $total - $discount);
        $set('../../remaining_amount', max(0, $netTotal - $paid_amount));
    }
}

?>
