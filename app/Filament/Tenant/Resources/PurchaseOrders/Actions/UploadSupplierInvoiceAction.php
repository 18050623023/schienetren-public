<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Actions;

use App\Models\Tenant\PurchaseOrder;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class UploadSupplierInvoiceAction
{
    /**
     * Configure action for table row or page header
     *
     * @param mixed $action
     * @return mixed
     */
    public static function configure($action)
    {
        return $action
            ->label('Upload / Ganti Invoice')
            ->icon('heroicon-o-document-arrow-up')
            ->color('gray')
            ->visible(fn (PurchaseOrder $record) => !in_array($record->status, ['canceled']))
            ->modalHeading('Upload / Perbarui Invoice Supplier')
            ->modalDescription(fn (PurchaseOrder $record) => "PO: {$record->code} — Supplier: " . ($record->supplier->name ?? 'Supplier'))
            ->modalWidth('md')
            ->fillForm(fn (PurchaseOrder $record): array => [
                'attachment' => $record->attachment,
            ])
            ->schema([
                FileUpload::make('attachment')
                    ->label('Lampiran Invoice / Faktur Supplier')
                    ->disk('public')
                    ->directory('purchases/invoices')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->required()
                    ->helperText('Format: JPG, PNG, WEBP, atau PDF (Maks. 10MB)'),
            ])
            ->action(function (array $data, PurchaseOrder $record) {
                $record->update([
                    'attachment' => $data['attachment'],
                ]);

                Notification::make()
                    ->title('Invoice Supplier Berhasil Disimpan')
                    ->body('File lampiran invoice untuk PO ' . $record->code . ' telah berhasil diperbarui.')
                    ->success()
                    ->send();
            });
    }
}
