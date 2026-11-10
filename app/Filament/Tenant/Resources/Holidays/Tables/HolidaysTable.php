<?php

namespace App\Filament\Tenant\Resources\Holidays\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('scope')
                    ->label('Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('recurrence_type')
                    ->label('Repeat Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('branches')
                    ->label('Cabang')
                    ->badge()
                    ->state(function ($record) {
                        // dd($record);
                        if ($record->scope === 'branch') {
                            return $record->branches->pluck('name')->all();//->join(', ');
                        }
                        return 'Semua Cabang';

                    }),

                TextColumn::make('display_date')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('is_active'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
