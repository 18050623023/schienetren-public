<?php

namespace App\Filament\Tenant\Resources\WorkSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
// use Filament\Tables\Filters\TrashedFilter;
// use Illuminate\View\View;
// use Filament\Actions\RestoreAction;
// use Filament\Actions\ForceDeleteAction;
// use Filament\Actions\RestoreBulkAction;
// use Filament\Actions\ForceDeleteBulkAction;

class WorkSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                ToggleColumn::make('is_active'),
            ])
            ->filters([
                // TrashedFilter::make(),
                //
            ])
            ->recordActions([
                // RestoreAction::make(),
                // ForceDeleteAction::make(),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // RestoreBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
