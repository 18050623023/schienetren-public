<?php

namespace App\Filament\Admin\Resources\Invoices\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\MorphToSelect;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->required()
                    ->default(fn(): string => 'INV-' . strtoupper(uniqid()))    ,
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Section::make('Informasi Jumlah')
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('admin_fee')
                            ->required()
                            ->numeric()
                            ->default(fn(): int => settingCentral('admin_fee', 1000)),
                        Select::make('promo_id')
                            ->relationship('promo', 'code')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\Promo $record) => $record->code . ' - ' . ($record->type === 'percentage' ? $record->value . '%' : 'Rp ' . number_format($record->value, 0, ',', '.'))),
                        TextInput::make('discount_amount')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                        TextInput::make('unique_code')
                            ->required()
                            ->numeric()
                            ->default(fn(): int => rand(11, 99)),
                        TextInput::make('total_amount')
                            ->required()
                            ->numeric(),
                    ]),
                Section::make('Informasi Status Pembayaran')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options(app(DatabaseService::class)->getEnumValues('invoices','status'))
                            ->required()
                            ->default('unpaid'),
                        Select::make('payment_channel')
                            ->options(app(DatabaseService::class)->getEnumValues('invoices','payment_channel'))
                            ->required()
                            ->default('manual_transfer'),
                        DateTimePicker::make('paid_at'),
                    ]),
                Section::make('Invoice Items')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                        ->hiddenLabel()
                        ->relationship()
                            ->schema([
                                MorphToSelect::make('item') // Nama relasi morph di model InvoiceItem
                                    ->label('Pilih Item')
                                    ->columns(2)
                                    ->types([
                                        MorphToSelect\Type::make(\App\Models\Wallet::class)
                                            ->titleAttribute('user_id')
                                            ->label('Top Up Saldo'),
                                        MorphToSelect\Type::make(\App\Models\Plan::class)
                                            ->titleAttribute('name')
                                            ->label('Paket Langganan'),
                                        MorphToSelect\Type::make(\App\Models\Addon::class)
                                            ->titleAttribute('name')
                                            ->label('Addon Tambahan'),
                                    ])
                                    ->required()
                                    ->columnSpan(3),
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('qty')
                                            ->label('Jumlah')
                                            ->required()
                                            ->inlineLabel()
                                            ->numeric(),
                                        TextInput::make('price')
                                            ->label('Harga')
                                            ->required()
                                            ->inlineLabel()
                                            ->numeric()
                                            ->prefix('Rp'),
                                    ])
                                    ->columnSpan(2)
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
