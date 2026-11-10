<?php

namespace App\Filament\Tenant\Resources\FinanceExpenses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;

class FinanceExpensesTable
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
                    
                TextColumn::make('account.name')
                    ->label('Akun')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('reference_number')
                    ->label('No. Ref')
                    ->searchable(),
                    
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                    
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30)
                    ->searchable(),
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
