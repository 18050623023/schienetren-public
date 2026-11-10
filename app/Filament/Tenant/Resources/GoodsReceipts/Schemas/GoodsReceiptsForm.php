<?php

namespace App\Filament\Tenant\Resources\GoodsReceipts\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\PurchaseOrder;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Form;

class GoodsReceiptsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('code')
                    ->label('Goods Receipt Code')
                    ->default(fn () => rtrim(settingTenant('goods_receipt_prefix', 'GR'), '-') . '-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)))
                    ->required()
                    ->columnSpan(2),

                Section::make('Informasi Order')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Hidden::make('purchase_order_id')->required(),
                        Forms\Components\Select::make('purchase_order_id_display')
                            ->disabled()
                            ->columnSpan('full')
                            ->label('Purchase Order')
                            ->relationship('purchaseOrder', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->code)
                            ->required(),
                        Forms\Components\Select::make('created_by')
                            ->disabled()
                            ->label('Created Order By')
                            ->relationship('purchaseOrder.createdBy', 'name')
                            ->live()
                            ->dehydrated(),
                        Forms\Components\Select::make('supplier_id')
                            ->disabled()
                            ->label('Supplier')
                            ->live()
                            ->relationship('purchaseOrder.supplier', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                "{$record->name} - {$record->company}"
                            )
                            ->columns(3)
                            ->required(),
                        Forms\Components\Select::make('branch_id')
                            ->disabled()
                            ->label('Cabang')
                            ->relationship('purchaseOrder.branch', 'name')
                            ->required(),
                        Forms\Components\DatePicker::make('order_date')
                            ->disabled()
                            ->label('Tanggal Order')
                            // ->default(now())
                            ->required(),
                        Forms\Components\TextArea::make('order_notes')
                            ->disabled()
                            ->label('Catatan Order')
                            ->columnSpan(2),
                    ]),

                Section::make('Informasi Penerimaan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('received_by')
                            ->relationship('receivedBy', 'name')
                            ->label('Received By')
                            ->afterStateHydrated(function ($state, $set) {
                                if (empty($state)) {
                                    $set('received_by', Auth::id());
                                }
                            })

                            ->live()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\DatePicker::make('date')
                            ->default(now())
                            ->label('Received Date')
                            ->required(),

                        Forms\Components\TextArea::make('notes')
                            ->label('Catatan')
                            ->columnSpan(2),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran / Foto Surat Jalan (Delivery Note)')
                            ->disk('public')
                            ->directory('goods-receipts/proofs')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->maxSize(10240)
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->helperText('Unggah foto/scan surat jalan dari ekspedisi atau supplier (Maks. 10MB)')
                            ->columnSpan(2),
                    ]),

                Section::make('Items')
                    ->columnSpanFull()
                    ->description('Tambahkan produk dan jumlahnya.')
                    ->schema(SectionItemFormGoodsReceipt::schema()),

            ]);
    }
}
