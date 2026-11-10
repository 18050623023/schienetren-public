<?php

namespace App\Filament\Tenant\Resources\Repairs\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Enums\RepairDeviceStatus;

// Tidak Di Pakai
class RepairInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Servis')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('code')->label('Repair Code'),
                            TextEntry::make('client.name')->label('Klien'),
                            TextEntry::make('status')
                                ->badge(),
                        ]),
                    ]),

                Section::make('Status Perangkat')
                    ->schema([
                        RepeatableEntry::make('repairDevices')
                            ->label('')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('deviceType.name')->label('Tipe'),
                                    TextEntry::make('brand.name')->label('Merek'),
                                    TextEntry::make('model')->label('Model'),
                                    TextEntry::make('status')
                                        ->label('Status Pengerjaan')
                                        ->badge(),
                                    TextEntry::make('problems')->label('Keluhan')->columnSpan(2),
                                    TextEntry::make('diagnosis')
                                ])
                            ])
                    ]),
            ]);
    }
}
