<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Pages;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Filament\Tenant\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \App\Filament\Tenant\Resources\PurchaseOrders\Actions\PaySupplierDebtAction::configure(
                \Filament\Actions\Action::make('pay_debt')
            ),

            \App\Filament\Tenant\Resources\PurchaseOrders\Actions\UploadSupplierInvoiceAction::configure(
                \Filament\Actions\Action::make('upload_invoice')
            ),

            \Filament\Actions\Action::make('receive')
                ->label('Penerimaan Barang')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn ($record) => in_array($record->status, ['confirmed','partially']))
                ->url(fn ($record) => route('filament.tenant.resources.goods-receipts.create', [
                    'poid' => \Vinkla\Hashids\Facades\Hashids::encode($record->id)
                ])),

            \Filament\Actions\Action::make('Confirm')
                ->icon('heroicon-s-arrow-up-on-square-stack')
                ->color('success')
                ->visible(fn ($record) => in_array($record->status, ['draft']))
                ->requiresConfirmation()
                ->modalHeading('Confirm Purchase Order')
                ->modalDescription('Yakin Konfirmasi Purchase Order? Data tidak dapat diubah setelah dikonfirmasi.')
                ->action(fn (\App\Models\Tenant\PurchaseOrder $record) =>
                    \App\Filament\Tenant\Resources\PurchaseOrders\Actions\PurchaseOrderActionStatus::confirmAction($record)
                ),

            EditAction::make()
                ->visible(fn ($record) => in_array($record->status, ['draft'])),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    // public function getRecord(): Model
    // {
    //     $record = parent::getRecord();

    //     dd($record);

    //     return $record;
    // }



    // public static function getRelationship(): string
    // {
    //     return 'goodsReceipts';
    // }


}
