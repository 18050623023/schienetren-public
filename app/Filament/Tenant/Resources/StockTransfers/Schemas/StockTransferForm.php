<?php

namespace App\Filament\Tenant\Resources\StockTransfers\Schemas;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\StockTransfer;
use App\Models\Tenant\StockTransferItem;
use App\Services\DatabaseService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StockTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        TextInput::make('code')
                            ->label('Nomor Mutasi')
                            ->default(fn () => rtrim(settingTenant('stock_transfer_prefix', 'TRF'), '-') . '-' . date('Ym') . '-' . strtoupper(Str::random(4)))
                            ->readOnly()
                            ->required()
                            ->unique(ignoreRecord: true),
                            
                        DatePicker::make('date')
                            ->label('Tanggal Mutasi')
                            ->default(now())
                            ->disabled(fn (?StockTransfer $record) => $record && $record->status === 'in_transit')
                            ->required(),

                        Select::make('from_branch_id')
                            ->label('Cabang Asal')
                            ->relationship('fromBranch', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(fn () => session('current_branch_id'))
                            ->disabled(fn (?StockTransfer $record) => ($record && $record->status === 'in_transit') || !Auth::user()->hasRole(['owner', 'admin'])) // Sesuaikan dengan role jika perlu
                            ->dehydrated()
                            ->required()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // Ambil seluruh baris yang ada di repeater 'items'
                                $items = $get('items') ?? [];
                                
                                // Kosongkan field terkait produk untuk setiap baris
                                foreach ($items as $key => $item) {
                                    $items[$key]['product_display_name'] = null;
                                    $items[$key]['product_id'] = null;
                                    $items[$key]['product_variant_id'] = null;
                                    // Anda mungkin juga ingin mereset itemSerials atau qty jika perlu
                                    // $items[$key]['qty_sent'] = 0;
                                    // $items[$key]['itemSerials'] = [];
                                }
                                
                                // Simpan kembali ke state repeater
                                $set('items', $items);
                            }),
                            
                        Select::make('to_branch_id')
                            ->label('Cabang Tujuan')
                            ->relationship('toBranch', 'name')
                            ->searchable()
                            ->preload()
                            ->different('from_branch_id')
                            ->disabled(fn (?StockTransfer $record) => $record && $record->status === 'in_transit')
                            ->required(),
                        // ToggleButtons::make('status')
                        //     ->options(app(DatabaseService::class)->getEnumValues('stock_transfer','status'))
                        //     ->inline()
                        //     ->default('draft')
                        //     ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->disabled(fn (?StockTransfer $record) => $record && $record->status === 'in_transit'),
                ]),
                Repeater::make('items')
                    ->columnSpanFull()
                    ->relationship('items')
                    ->columns(6)
                    ->schema([
                        Hidden::make('product_id')
                            ->required(),

                        Hidden::make('product_variant_id')
                            ->required(),

                        Hidden::make('has_sn')
                            ->default(false)
                            ->afterStateHydrated(function ($component, $state, $record, Get $get) {
                                if ($productId = $get('product_id')) {
                                    $component->state((bool) Product::where('id', $productId)->value('has_sn'));
                                }
                            }),

                        TextInput::make('product_display_name')
                            ->label('Produk & Varian')
                            ->placeholder('Klik "Pilih Produk" untuk memilih...')
                            ->readOnly()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state, $record, Get $get) {
                                if ($variantId = $get('product_variant_id')) {
                                    $variant = ProductVariant::with(['product.brand'])->find($variantId);
                                    if ($variant) {
                                        $component->state(trim(($variant->product?->brand?->name ?? '') . ' ' . ($variant->product?->name ?? '') . ($variant->name ? ' - ' . $variant->name : '')));
                                    }
                                }
                            })
                            ->columnSpan(2)
                            ->required(fn ($get) => blank($get('product_variant_id')))
                            ->validationMessages(['required' => 'Silakan pilih produk terlebih dahulu.']),

                        // Tombol Pilih Produk / Varian via Modal Livewire
                        Actions::make([
                            Action::make('pilih_produk')
                                ->label(fn ($get) => filled($get('product_variant_id')) ? 'Ganti Produk' : 'Pilih Produk')
                                ->icon('heroicon-m-magnifying-glass')
                                ->button()
                                ->disabled(fn ($get, ?StockTransferItem $record) => ($record && $record->stockTransfer->status === 'in_transit') || blank($get('../../from_branch_id')))
                                ->modalHeading('Pilih Produk / Varian')
                                ->modalWidth('5xl')
                                ->modalSubmitAction(false)
                                ->modalCancelAction(false)
                                ->modalContent(fn ($get, $component) => view('livewire.components.table-selector-modal-wrapper', [
                                    'preset' => 'product_variant',
                                    'params' => ['branch_id' => $get('../../from_branch_id')],
                                    'statePath' => $component->getStatePath(),
                                ]))
                        ])
                        ->hiddenOn('view')
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'flex:1;display:flex;justify-content:center']),

                        TextInput::make('qty_sent')
                            ->label('Qty Dikirim')
                            ->numeric()
                            ->default(0)
                            ->minValue(1)
                            ->disabled(fn ($get, ?StockTransferItem $record) => ($record && $record->stockTransfer->status === 'in_transit') || (bool) $get('has_sn') || blank($get('product_variant_id')))
                            ->helperText(function (Get $get) {
                                $variantId = $get('product_variant_id');
                                $branchId = $get('../../from_branch_id');
                                if (!$variantId || !$branchId) return null;
                                $stock = ProductVariant::find($variantId)?->getStock($branchId) ?? 0;
                                return "Stok tersedia: {$stock}";
                            })
                            ->dehydrated(true)
                            ->columnSpan(1)
                            ->required(),

                        TextInput::make('qty_received')
                            ->label('Qty Diterima')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->hidden(fn (?StockTransferItem $record) => !$record || !in_array($record->stockTransfer->status, ['in_transit', 'completed', 'received']))
                            ->disabled() // Selalu disabled di form utama karena diubah melalui Action Selesaikan Penerimaan
                            ->dehydrated(true)
                            ->columnSpan(1)
                            ->required(fn (?StockTransferItem $record) => $record && $record->stockTransfer->status === 'in_transit'),
                        
                        // Tombol pilih serial yang membuka Modal Livewire
                        Actions::make([
                            Action::make('pilih_serial')
                                ->label('Pilih Serial Number')
                                ->icon('heroicon-m-qr-code')
                                ->button()
                                ->disabled(fn ($get, ?StockTransferItem $record) => ($record && $record->stockTransfer->status === 'in_transit') || blank($get('product_variant_id')) || ! (bool) $get('has_sn'))
                                ->modalHeading('Pilih Serial Number')
                                ->modalWidth('4xl')
                                ->modalSubmitAction(false)
                                ->modalCancelAction(false)
                                ->modalContent(fn ($get, $component) => view('livewire.components.table-selector-modal-wrapper', [
                                    'preset' => 'product_serial',
                                    'params' => [
                                        'productId' => $get('product_id'),
                                        'variantId' => $get('product_variant_id'),
                                        'branchId' => $get('../../from_branch_id'),
                                    ],
                                    'statePath' => $component->getStatePath(),
                                ]))
                        ])
                        ->hiddenOn('view')
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'flex:1;display:flex;justify-content:center']),

                        // Nested Repeater untuk menyimpan Serial yang terpilih
                        Repeater::make('itemSerials')
                            ->relationship('itemSerials')
                            ->defaultItems(0)
                            ->label('Serial Terpilih')
                            ->visible(fn ($get) => (bool) $get('has_sn'))
                            ->schema([
                                Hidden::make('product_serial_id'),
                                TextInput::make('serial_number')
                                    ->disabled()
                                    ->hiddenLabel()
                                    ->dehydrated(false) // hanya untuk display jika butuh
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && $record->productSerial) {
                                            $component->state($record->productSerial->serial_number);
                                        }
                                    })
                            ])
                            ->addable(false) // user tidak bisa add manual via repeater default
                            ->deletable(fn (?StockTransferItem $record) => !$record || $record->stockTransfer->status !== 'in_transit')
                            ->columnSpanFull()
                    ])
                    ->defaultItems(1)
                    ->addable(fn (?StockTransfer $record) => !$record || $record->status !== 'in_transit')
                    ->deletable(fn (?StockTransfer $record) => !$record || $record->status !== 'in_transit')
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
