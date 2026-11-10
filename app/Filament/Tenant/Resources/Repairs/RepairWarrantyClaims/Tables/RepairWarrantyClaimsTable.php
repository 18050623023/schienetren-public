<?php

namespace App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepairWarrantyClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('repairItemWarranty.repairItem.repairDevice.repair.code')
                    ->label('Kode Perbaikan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('repairItemWarranty.repairItem.productVariant.name')
                    ->label('Produk/Servis')
                    ->state(function ($record) {
                        $productName = $record->repairItemWarranty->repairItem->productVariant->product->name ?? '';
                        $variantName = $record->repairItemWarranty->repairItem->productVariant->name ?? '';
                        return trim($productName . ' ' . $variantName);
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('claim_date')
                    ->label('Tanggal Klaim')
                    ->date()
                    ->sortable(),
                TextColumn::make('problems')
                    ->label('Keluhan')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->refund_payment_id) {
                            return 'Diretur & Refund';
                        }
                        return match ($record->status) {
                            'pending' => 'Pending',
                            'in_progress' => 'Diproses',
                            'completed' => 'Selesai',
                            'rejected' => 'Ditolak',
                            default => $record->status,
                        };
                    })
                    ->color(function ($record): string {
                        if ($record->refund_payment_id) {
                            return 'danger';
                        }
                        return match ($record->status) {
                            'pending' => 'warning',
                            'in_progress' => 'info',
                            'completed' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('refund_amount')
                    ->label('Refund')
                    ->money('IDR')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('handledBy.name')
                    ->label('Ditangani Oleh')
                    ->searchable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'Diproses',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ])
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
