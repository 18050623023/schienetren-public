<?php

namespace App\Filament\Tenant\Resources\StockMovements\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Mutasi')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('branch.name')->label('Lokasi Cabang'),
                        TextEntry::make('created_at')->label('Tanggal & Waktu')->dateTime(),
                        
                        TextEntry::make('direction')
                            ->label('Jenis Pergerakan')
                            ->badge()
                            ->color(fn (string $state) => match($state) {
                                'in' => 'success',
                                'out' => 'danger',
                                'adjust' => 'warning',
                            }),
                            
                        TextEntry::make('quantity')
                            ->label('Kuantitas')
                            ->numeric(),
                    ]),

                Section::make('Detail Item & Dokumen')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Produk')
                            ->formatStateUsing(function ($record) {
                                $name = $record->product->name;
                                if ($record->product_variant_id) {
                                    $name .= ' (' . $record->productVariant->name . ')';
                                }
                                return $name;
                            }),
                            
                        TextEntry::make('source_type')
                            ->label('Dokumen Referensi')
                            ->badge()
                            ->color(function ($state) {
                                $base = class_basename($state);
                                return match ($base) {
                                    'SaleItem' => 'info',
                                    'RepairItem' => 'warning',
                                    'StockOpname' => 'gray',
                                    'purchase' => 'success',
                                    'stock_transfer_ship' => 'danger',
                                    'stock_transfer_receive' => 'info',
                                    'RepairWarrantyClaim' => 'danger',
                                    default => 'secondary',
                                };
                            })
                            ->formatStateUsing(function ($record) {
                                $base = class_basename($record->source_type);
                                $label = match ($base) {
                                    'SaleItem' => 'Penjualan POS',
                                    'RepairItem' => 'Servis / Reparasi',
                                    'StockOpname' => 'Stock Opname',
                                    'purchase' => 'Penerimaan (PO)',
                                    'stock_transfer_ship' => 'Transfer Keluar',
                                    'stock_transfer_receive' => 'Transfer Masuk',
                                    'RepairWarrantyClaim' => 'Klaim Garansi',
                                    'Product' => 'Saldo Awal',
                                    default => $base,
                                };
                                return $label . ($record->source_id ? ' (Ref ID: #' . $record->source_id . ')' : '');
                            })
                            ->url(fn ($record) => self::resolveSourceUrl($record), shouldOpenInNewTab: true)
                            ->suffixAction(
                                Action::make('view_source')
                                    ->label('Buka Dokumen')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->color('primary')
                                    ->tooltip('Buka Dokumen Referensi')
                                    ->visible(fn ($record) => (bool) self::resolveSourceUrl($record))
                                    ->url(fn ($record) => self::resolveSourceUrl($record), shouldOpenInNewTab: true)
                            ),

                        TextEntry::make('note')
                            ->label('Catatan Mutasi')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function resolveSourceUrl($record): ?string
    {
        if (!$record || !$record->source_type) {
            return null;
        }

        $base = class_basename($record->source_type);

        try {
            switch ($base) {
                case 'SaleItem':
                    $invoice = null;
                    try {
                        $invoice = $record->source?->sale?->invoice_number;
                    } catch (\Throwable) {}

                    if (!$invoice && preg_match('/#([A-Za-z0-9\-]+)/', $record->note ?? '', $matches)) {
                        $invoice = $matches[1];
                    }
                    $url = \App\Filament\Tenant\Resources\Sales\SaleResource::getUrl('index', panel: 'tenant');
                    return $invoice ? $url . '?tableSearch=' . urlencode($invoice) : $url;

                case 'RepairItem':
                    $code = null;
                    try {
                        $code = $record->source?->repairDevice?->repair?->code;
                    } catch (\Throwable) {}

                    if (!$code && preg_match('/(?:reparasi|servis)\s+([A-Za-z0-9\-]+)/i', $record->note ?? '', $matches)) {
                        $code = $matches[1];
                    }
                    $url = \App\Filament\Tenant\Resources\Repairs\RepairResource::getUrl('index', panel: 'tenant');
                    return $code ? $url . '?tableSearch=' . urlencode($code) : $url;

                case 'StockOpname':
                    if ($record->source_id) {
                        return \App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource::getUrl('view', ['record' => $record->source_id], panel: 'tenant');
                    }
                    return \App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource::getUrl('index', panel: 'tenant');

                case 'stock_transfer_ship':
                case 'stock_transfer_receive':
                    if ($record->source_id) {
                        return \App\Filament\Tenant\Resources\StockTransfers\StockTransferResource::getUrl('edit', ['record' => $record->source_id], panel: 'tenant');
                    }
                    return \App\Filament\Tenant\Resources\StockTransfers\StockTransferResource::getUrl('index', panel: 'tenant');

                case 'purchase':
                    if ($record->source_id) {
                        return \App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource::getUrl('view', ['record' => $record->source_id], panel: 'tenant');
                    }
                    return \App\Filament\Tenant\Resources\GoodsReceipts\GoodsReceiptsResource::getUrl('index', panel: 'tenant');

                case 'RepairWarrantyClaim':
                    if ($record->source_id) {
                        return \App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\RepairWarrantyClaimResource::getUrl('edit', ['record' => $record->source_id], panel: 'tenant');
                    }
                    return \App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\RepairWarrantyClaimResource::getUrl('index', panel: 'tenant');

                case 'Product':
                    if ($record->source_id) {
                        return \App\Filament\Tenant\Resources\Products\ProductResource::getUrl('edit', ['record' => $record->source_id], panel: 'tenant');
                    }
                    return \App\Filament\Tenant\Resources\Products\ProductResource::getUrl('index', panel: 'tenant');

                default:
                    return null;
            }
        } catch (\Throwable $e) {
            return null;
        }
    }
}
