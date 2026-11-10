<?php

namespace App\Filament\Tenant\Resources\FinanceJournals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;

class FinanceJournalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('reference_number')
                    ->label('No. Ref')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->searchable(),
                    
                TextColumn::make('items_sum_debit')
                    ->label('Total Debit')
                    ->sum('items', 'debit')
                    ->money('IDR')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
