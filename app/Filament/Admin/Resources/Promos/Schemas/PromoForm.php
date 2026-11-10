<?php

namespace App\Filament\Admin\Resources\Promos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PromoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('code')
                            ->label('Kode Promo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                        TextInput::make('name')
                            ->label('Nama Promo')
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('target')
                            ->label('Target Diskon')
                            ->options([
                                'all' => 'Semua Transaksi',
                                'topup' => 'Khusus Topup',
                                'plan' => 'Khusus Base Plan',
                                'addon' => 'Khusus Addon',
                            ])
                            ->default('all')
                            ->required(),
                        Select::make('type')
                            ->label('Tipe Diskon')
                            ->options([
                                'fixed' => 'Nominal Tetap (Rp)',
                                'percentage' => 'Persentase (%)',
                            ])
                            ->default('fixed')
                            ->live()
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('value')
                            ->label('Nilai Diskon')
                            ->required()
                            ->numeric()
                            ->prefix(fn (Get $get) => $get('type') === 'percentage' ? '%' : 'Rp'),
                        TextInput::make('max_discount')
                            ->label('Maksimal Diskon (Rp)')
                            ->numeric()
                            ->visible(fn (Get $get) => $get('type') === 'percentage')
                            ->helperText('Kosongkan jika tidak ada batas maksimal.'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('min_spend')
                            ->label('Minimal Transaksi (Rp)')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('usage_limit')
                            ->label('Batas Penggunaan (Total)')
                            ->numeric()
                            ->helperText('Berapa kali promo ini bisa dipakai (kosong = tanpa batas).'),
                    ]),
                ]),

                Section::make('Masa Berlaku')->schema([
                    Grid::make(2)->schema([
                        DateTimePicker::make('start_date')
                            ->label('Berlaku Mulai'),
                        DateTimePicker::make('end_date')
                            ->label('Berakhir Pada')
                            ->afterOrEqual('start_date'),
                    ]),
                    Toggle::make('is_active')
                        ->label('Promo Aktif')
                        ->default(true)
                        ->required(),
                ]),
            ]);
    }
}
