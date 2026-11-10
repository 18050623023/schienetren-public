<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Schemas;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use Filament\Forms\Components\Repeater;
use Filament\Forms;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Form;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembelian')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->default(fn () => rtrim(settingTenant('po_prefix', 'PO'), '-') . '-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)))
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Select::make('created_by')
                            ->relationship('createdBy', 'name')
                            ->label('Created By')
                            ->default(Auth::user()->id)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                "{$record->name} - {$record->company}"
                            )
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Supplier Name')
                                    // ->columnSpan(1)
                                    ->required(),
                                Forms\Components\TextInput::make('company')
                                    ->required(),
                                Forms\Components\TextInput::make('whatsapp')
                                    ->prefix('+62')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextArea::make('address')
                                    ->columnSpan(3)
                                    ->required(),
                                Forms\Components\TextArea::make('description')
                                    ->columnSpan(3),
                            ])
                            ->columns(3)
                            ->required(),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->default(fn () => session('current_branch_id') ?? 1)
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextArea::make('notes')
                            ->label('Catatan')
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran Invoice / Faktur Supplier')
                            // ->disk('public')
                            ->directory('purchases/invoices')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->maxSize(10240)
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->helperText('Unggah foto/PDF faktur atau invoice dari supplier (Maks. 10MB)')
                            ->columnSpan(2),
                    ]),

                Section::make('Informasi Pembayaran')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->columnSpanFull()
                            ->default('cash')
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                                'credit' => 'Credit',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Total Pembayaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->live(debounce: 800)
                            ->afterStateUpdated(fn ($get, $set) => self::hitungSisa($get, $set))
                            ,

                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live(debounce: 800)
                            ->afterStateUpdated(fn ($get, $set) => self::hitungSisa($get, $set))
                            ,
                        Forms\Components\TextInput::make('total')
                            ->label('Total Pembelian')
                            ->disabled()->dehydrated(true)
                            // ->readOnly()
                            ->numeric()
                            ->prefix('Rp')
                            ->live(debounce: 800)
                            ->required()
                            ->afterStateUpdated(fn ($get, $set) => self::hitungSisa($get, $set))
                            ,
                        Forms\Components\TextInput::make('remaining_amount')
                            ->label('Sisa Pembayaran')
                            ->disabled()->dehydrated()
                            ->numeric()
                            ->prefix('Rp')
                            ->live(debounce: 800)
                            ->required(),
                    ]),

                Section::make('Items')
                    ->columnSpanFull()
                    ->description('Tambahkan produk dan jumlahnya.')
                    ->schema(SectionItemForm::schema()),

            ]);
    }

    public static function hitungSisa($get, $set)
    {
        $total = (float) ($get('total') ?? 0);
        $discount = (float) ($get('discount') ?? 0);
        $paid_amount = (float) ($get('paid_amount') ?? 0);

        $netTotal = max(0, $total - $discount);
        $set('remaining_amount', max(0, $netTotal - $paid_amount));
    }
}
