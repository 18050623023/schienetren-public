<?php

namespace App\Filament\Admin\Resources\Tenants\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('business_name')
                    ->searchable(),
                TextColumn::make('domains.domain')
                    ->label('Domain')
                    ->formatStateUsing(fn ($state, $record) => $record->domains->pluck('domain')->join(', '))
                    ->url(fn ($record) => optional($record->domains->first())->domain ? ('https://' . optional($record->domains->first())->domain) : null)
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('subscription.plan.name')
                    ->label('Subscription')
                    ->placeholder('No Active Subscription'),
                ToggleColumn::make('is_active'),
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
                // TrashedFilter::make(),
            ])
            ->recordActions([

                Action::make('sync_features')
                    ->label('Sync Features')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Ulang Fitur')
                    ->modalDescription('Tindakan ini akan menghitung ulang seluruh limit fitur tenant berdasarkan Plan dan Addon terbarunya. Lanjutkan?')
                    ->action(function ($record) {
                        // dd($record->subscription());
                        // Asumsi $record adalah model Subscription
                        \App\Services\TenantManagerService::syncSubscriptionFeatures($record->subscription);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Fitur berhasil disinkronkan!')
                            ->success()
                            ->send();
                    })
                ,
                EditAction::make(),
                DeleteAction::make(),
                // ForceDeleteAction::make(),
                // RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    // RestoreBulkAction::make(),
                ]),
            ]);
    }
}
