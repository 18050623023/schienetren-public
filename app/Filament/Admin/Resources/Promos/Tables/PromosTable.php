<?php

namespace App\Filament\Admin\Resources\Promos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Promo')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('name')
                    ->label('Nama Promo')
                    ->searchable(),
                TextColumn::make('target')
                    ->label('Target')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'Semua',
                        'topup' => 'Topup',
                        'plan' => 'Plan',
                        'addon' => 'Addon',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'gray',
                        'topup' => 'success',
                        'plan' => 'info',
                        'addon' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'percentage' ? 'info' : 'success')
                    ->formatStateUsing(fn (string $state): string => $state === 'percentage' ? 'Persen (%)' : 'Nominal (Rp)'),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->sortable()
                    ->formatStateUsing(function (string $state, $record) {
                        if ($record->type === 'percentage') {
                            return (float) $state . '%';
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    }),
                TextColumn::make('usage_limit')
                    ->label('Kuota')
                    ->placeholder('Tanpa Batas')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
