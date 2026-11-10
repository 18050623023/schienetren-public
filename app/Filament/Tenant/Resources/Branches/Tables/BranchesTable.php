<?php

namespace App\Filament\Tenant\Resources\Branches\Tables;
use EduardoRibeiroDev\FilamentLeaflet\Layers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Tables\MapColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),

                MapColumn::make('location')
                    ->height(100)
                    ->zoom(14)
                    ->state(function ($record) {
                        if ($record->latitude === null || $record->longitude === null) {
                            return null;
                        }

                        return [
                            'lat' => $record->latitude,
                            'lng' => $record->longitude,
                        ];
                    })
                    ->pickMarker(fn(Marker $marker) => $marker->black())
                    ->static(),    // Disable interactions in table preview

                ToggleColumn::make('is_active')
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            $check = \App\Models\Tenant\Branch::checkActiveLimit($record);

                            if (! $check->passed) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title($check->message)
                                    ->send();
                                    
                                return ['error' => $check->message];
                            }
                        }

                        $record->is_active = $state;
                        $record->save();

                        return $state;
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                TrashedFilter::make(),
                //
            ])
            ->recordActions([
                RestoreAction::make(),
                ForceDeleteAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
