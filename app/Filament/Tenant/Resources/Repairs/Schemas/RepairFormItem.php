<?php

namespace App\Filament\Tenant\Resources\Repairs\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

class RepairFormItem
{
    public static function schema(?Get $parentGet = null, \Closure $checkAddItems): Section
    {
        return Section::make('reparation_items')
            ->heading('Pemakaian Alokasi Sparepart Gudang')
            ->icon('heroicon-o-cube')
            // ->columnSpanFull()
            // ->columnSpan(7)
            ->visible(fn(Get $get, $livewire) => $checkAddItems($get, $livewire))
            ->headerActions([
                // Tombol Hijau Pemanggil Modal Inventori (Gambar 2)
                Action::make('open_inventory_modal')
                    ->label('Tambah Item')
                    ->icon('heroicon-m-plus')
                    ->color('success')
                    ->button()
                    ->disabled(function (Get $get, string $operation) use ($parentGet) {
                        if ($operation === 'view') {
                            return true;
                        }

                        return empty(RepairFormDevice::getNestedFieldValue($get, 'branch_id', $parentGet)) && empty(session('current_branch_id'));
                    })
                    ->modalHeading('Inventori Stok Sparepart Gudang')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function ($component, Get $get) use ($parentGet) {
                        $containerStatePath = $component->getContainer()->getStatePath();
                        // dd($containerStatePath);
                        $targetStatePath = $containerStatePath ? "{$containerStatePath}.repair_items" : 'repair_items';

                        $branchId = empty(session('current_branch_id')) ? RepairFormDevice::getNestedFieldValue($get, 'branch_id', $parentGet) : session('current_branch_id');

                        // dd($branchId);

                        $repairItems = $get('repair_items') ?? [];
                        $usedQuantities = [];
                        foreach ($repairItems as $item) {
                            $variantId = $item['product_variant_id'] ?? null;
                            if ($variantId) {
                                $qty = (int) ($item['qty'] ?? 1);
                                $usedQuantities[$variantId] = ($usedQuantities[$variantId] ?? 0) + $qty;
                            }
                        }

                        return view('livewire.components.table-selector-modal-wrapper', [
                            'preset' => 'product_variant',
                            'params' => [
                                'branch_id' => $branchId,
                                'used_quantities' => $usedQuantities,
                            ],
                            'statePath' => $targetStatePath,
                            'eventName' => 'repair-item-selected',
                        ]);
                    }),

                Action::make('open_service_modal')
                    ->label('Tambah Jasa')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->color('info')
                    ->button()
                    
                    ->disabled(function (Get $get, string $operation) use ($parentGet) {
                        if ($operation === 'view') {
                            return true;
                        }

                        return empty(RepairFormDevice::getNestedFieldValue($get, 'branch_id', $parentGet)) && empty(session('current_branch_id'));
                    })
                    // ->disabled(function (Get $get) use ($parentGet) {
                    //     return empty(RepairFormDevice::getNestedFieldValue($get, 'branch_id', $parentGet)) && empty(session('current_branch_id'));
                    // })
                    ->modalHeading('Pilih Layanan Jasa / Service')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function ($component, Get $get) use ($parentGet) {
                        $containerStatePath = $component->getContainer()->getStatePath();
                        $targetStatePath = $containerStatePath ? "{$containerStatePath}.repair_items" : 'repair_items';

                        return view('livewire.components.table-selector-modal-wrapper', [
                            'preset' => 'service_variant',
                            'params' => [],
                            'statePath' => $targetStatePath,
                            'eventName' => 'repair-item-selected',
                        ]);
                    }),
            ])
            ->schema([
                Repeater::make('repair_items')
                    // ->hiddenLabel()
                    ->live()
                    ->columns(5)
                    ->default([])
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->table([
                        TableColumn::make('name'),
                        TableColumn::make('price')
                            ->width('150px'),
                        TableColumn::make('warranty')
                            ->width('100px'),
                        TableColumn::make('qty')
                            ->width('40px'),
                        TableColumn::make('sn'),
                        // TableColumn::make('subtotal')
                        //     ->width('150px'),
                    ])
                    ->compact()
                    ->schema([
                        Hidden::make('product_id'),
                        Hidden::make('product_variant_id'),
                        Hidden::make('product_serial_id'),
                        Hidden::make('has_sn')
                            ->default(false),
                        Hidden::make('max_stock'),

                        Textarea::make('name')
                            ->columnSpan(2)
                            ->autosize()
                            ->rows(1)
                            ->readOnly(),

                        TextInput::make('price')
                            // ->label('Harga Jual')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('warranty')
                            // ->label('Garansi')
                            ->readOnly(),

                        TextInput::make('qty')
                            // ->label('Qty')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(fn(Get $get) => filled($get('max_stock')) ? $get('max_stock') : null)
                            ->disabled(fn(Get $get) => (bool) $get('has_sn'))
                            ->dehydrated(true)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $price = (float) ($get('price') ?? 0);
                                $qty = (int) ($get('qty') ?? 1);
                                $set('subtotal', $price * $qty);
                                $set('subtotal_item', $price * $qty);
                            }),

                        TextInput::make('sn')
                            // ->label('No. SN')
                            ->readOnly()
                            ->required(fn(Get $get) => (bool) $get('has_sn'))
                            ->placeholder(fn(Get $get) => $get('has_sn') ? 'Pilih Serial Number' : 'Non-SN')
                            ->default('Non-SN')
                            ->suffixAction(
                                Action::make('select_serial')
                                    ->disabled(fn($operation)=>$operation==='view')
                                    ->icon('heroicon-m-qr-code')
                                    ->tooltip('Pilih Serial Number')
                                    ->visible(fn(Get $get) => (bool) $get('has_sn'))
                                    ->modalHeading('Pilih Serial Number')
                                    ->modalWidth(Width::FourExtraLarge)
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Tutup')
                                    ->modalContent(function (Get $get, $component) use ($parentGet) {
                                        $branchId = session('current_branch_id')
                                            ?? RepairFormDevice::getNestedFieldValue($get, 'branch_id', $parentGet);

                                        return view('livewire.components.table-selector-modal-wrapper', [
                                            'preset' => 'product_serial',
                                            'params' => [
                                                'productId' => $get('product_id'),
                                                'variantId' => $get('product_variant_id'),
                                                'branchId' => $branchId,
                                            ],
                                            'statePath' => $component->getStatePath(),
                                            'eventName' => 'repair-serial-selected',
                                        ]);
                                    })
                            ),

                        TextInput::make('subtotal')
                            // ->label('Sub Total')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),
                    ]),

                Placeholder::make('grand_total_sparepart')
                    // ->hiddenLabel()
                    ->content(function (Get $get) {
                        $items = $get('repair_items') ?? [];
                        $total = collect($items)->sum(fn($item) => (float) ($item['subtotal'] ?? (($item['price'] ?? 0) * ($item['qty'] ?? 1))));

                        return new HtmlString('
                        <div class="flex justify-end items-center gap-4 text-base font-bold p-3 border-t border-gray-200 dark:border-gray-700">
                            <span>Sub Total Sparepart:</span>
                            <span class="text-primary-600 dark:text-primary-400 text-lg">Rp ' . number_format($total, 0, ',', '.') . '</span>
                        </div>
                    ');
                    }),
            ]);
    }
}
