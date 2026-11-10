<?php

namespace App\Filament\Tenant\Resources\WorkShifts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\View\View;

class WorkShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('start_time')
                    ->searchable(),
                TextColumn::make('end_time')
                    ->searchable(),
                TextColumn::make('break_minutes')
                    ->searchable(),
                TextColumn::make('late_tolerance'),
                TextColumn::make('overtime_tolerance'),


            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
