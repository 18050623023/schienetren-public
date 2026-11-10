<?php

namespace App\Filament\Tenant\Resources\LeaveTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LeaveTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('default_quota')
                    ->searchable()
                    ->placeholder('Unlimited')
                    // ->formatStateUsing(function ($state, $record) {
                    //     // dd($record);
                    //     return ($record->is_unlimited) ? 'Unlimited' : $state;
                    // })
                    ,
                TextColumn::make('reset_period')
                    ->searchable(),
                ToggleColumn::make('is_paid')
                    ->searchable(),
                ToggleColumn::make('require_approval')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
