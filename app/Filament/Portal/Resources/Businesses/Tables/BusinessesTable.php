<?php

namespace App\Filament\Portal\Resources\Businesses\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

class BusinessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')
                    ->label('Nama Bisnis')
                    ->searchable(),
                TextColumn::make('domain.domain')
                    ->label('Domain'),
                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            $user = Auth::user();
                            $activeSubscription = $user->getActiveSubscription();

                            if (!$activeSubscription) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal mengaktifkan')
                                    ->body('Anda tidak memiliki langganan aktif.')
                                    ->danger()
                                    ->send();

                                return $record->is_active;
                            }

                            $maxTenants = $activeSubscription->plan->getFeatureValue(\App\Enums\TenantFeature::TENANT) ?? 0;

                            $activeCount = $user->tenants()->where('is_active', true)->count();

                            if ($activeCount >= $maxTenants) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Batas Kuota Tercapai')
                                    ->body('Anda telah mencapai batas maksimal bisnis yang dapat diaktifkan sesuai paket berlangganan.')
                                    ->danger()
                                    ->send();

                                return $record->is_active;
                            }
                        }

                        $record->update(['is_active' => $state]);

                        \Filament\Notifications\Notification::make()
                            ->title('Status berhasil diubah')
                            ->success()
                            ->send();

                        return $state;
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('login_to_business')
                    ->label('Masuk ke Toko')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('primary')
                    ->url(fn(Tenant $record) => 'http://' . $record->domain?->domain . '/admin')
                    ->openUrlInNewTab(),
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
