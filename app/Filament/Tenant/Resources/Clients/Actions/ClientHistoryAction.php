<?php

namespace App\Filament\Tenant\Resources\Clients\Actions;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Client;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairDevice;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;

class ClientHistoryAction
{
    /**
     * Create a reusable History Reparasi action.
     *
     * @param string $actionClass The class of the action to instantiate (e.g. \Filament\Tables\Actions\Action::class or \Filament\Infolists\Components\Actions\Action::class)
     * @param string $name The name of the action (default 'history')
     * @return mixed
     */
    public static function make(string $actionClass, string $name = 'history')
    {
        return $actionClass::make($name)
            ->label('History Reparasi')
            ->icon('heroicon-m-clock')
            ->modalHeading('History Reparasi Client')
            ->modalWidth('5xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->infolist([
                RepeatableEntry::make('client_repairs')
                    ->label('Daftar Reparasi')
                    ->state(function (?Model $record) {
                        if (!$record) return [];

                        $client = null;
                        if ($record instanceof Client) {
                            $client = $record;
                        } elseif ($record instanceof Repair) {
                            $client = $record->client;
                        } elseif ($record instanceof RepairDevice) {
                            $client = $record->repair?->client;
                        }

                        if ($client) {
                            // Mengambil semua repair milik client tersebut beserta relasi devices-nya
                            return $client->repair()->with('repairDevices.brand')->orderBy('created_at', 'desc')->get();
                        }

                        return [];
                    })
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('code')->label('Kode Reparasi'),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('created_at')->label('Tanggal Masuk')->date(),
                            Actions::make([
                                Action::make('detail')
                                    ->label('Lihat Detail')
                                    ->icon('heroicon-m-eye')
                                    ->button()
                                    ->url(fn (Repair $record) => \App\Filament\Tenant\Resources\Repairs\RepairResource::getUrl('index') . '?repair=' . \Vinkla\Hashids\Facades\Hashids::encode($record->id))
                            ]),
                        ]),
                        RepeatableEntry::make('repairDevices')
                            ->label('Perangkat / Keluhan')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('brand.name')->label('Merek'),
                                    TextEntry::make('model')->label('Model'),
                                    TextEntry::make('color')->label('Warna'),
                                    TextEntry::make('problems')->label('Keluhan')->limit(50),
                                ])
                            ])
                            ->columnSpanFull()
                    ])
                    ->columnSpanFull()
            ]);
    }
}
