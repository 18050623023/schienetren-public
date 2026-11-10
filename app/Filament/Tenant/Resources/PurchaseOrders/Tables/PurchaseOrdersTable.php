<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Tables;

use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\PurchaseOrders\Actions\PaySupplierDebtAction;
use App\Filament\Tenant\Resources\PurchaseOrders\Actions\PurchaseOrderActionStatus;
use App\Filament\Tenant\Resources\PurchaseOrders\Actions\UploadSupplierInvoiceAction;
use App\Models\Tenant\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\HtmlString;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode PO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Total PO')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR', locale: 'id')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('remaining_amount')
                    ->label('Sisa Hutang')
                    ->money('IDR', locale: 'id')
                    ->badge()
                    ->color(fn ($state): string => (float)$state > 0 ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        // 'draft' => 'gray',
                        'confirmed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->date(),
                TextColumn::make('attachment')
                    ->label('Invoice')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ada' : '-')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                //
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    PaySupplierDebtAction::configure(Action::make('pay_debt')),
                    UploadSupplierInvoiceAction::configure(Action::make('upload_invoice')),

                    Action::make('receive')
                        ->label('Penerimaan Barang')
                        ->icon('heroicon-o-truck')
                        ->color('warning')
                        ->visible(fn ($record) => in_array($record->status, ['confirmed','partially']))
                        ->url(fn ($record) => route('filament.tenant.resources.goods-receipts.create', [
                            'poid' => Hashids::encode($record->id)
                        ])),

                    Action::make('Confirm')
                        ->icon('heroicon-s-arrow-up-on-square-stack')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status, ['draft']))
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Purchase Order')
                        ->modalDescription(new HtmlString(
                            'Yakin Confirmasi Purchase?. <br>' .
                            'Purchase tidak bisa diubah setelah di konfirmasi, ' .
                            'Pastikan semua isian sudah benar.'
                        ))
                        ->action(fn (PurchaseOrder $record) =>
                            PurchaseOrderActionStatus::confirmAction($record)
                        ),

                    Action::make('Cancel')
                        ->icon('heroicon-s-arrow-down-on-square-stack')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['draft']))
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Purchase Order')
                        ->modalDescription(new HtmlString(
                            'Yakin Cancel Purchase?. <br>' .
                            'Purchase tidak bisa diubah setelah di cancel, ' .
                            'Pastikan semua isian sudah benar.'
                        ))
                        ->action(fn (PurchaseOrder $record) =>
                            PurchaseOrderActionStatus::cancelAction($record)
                        ),

                    ViewAction::make()
                        ->icon('heroicon-s-eye')
                        ->label('View'),

                    EditAction::make()
                        ->visible(fn ($record) => in_array($record->status, ['draft'])),

                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
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
