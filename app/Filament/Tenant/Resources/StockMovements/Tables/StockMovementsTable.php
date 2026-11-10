<?php

namespace App\Filament\Tenant\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Mutasi')
                    ->dateTime('d M Y H:i'),
                    
                TextColumn::make('branch.name')
                    ->label('Cabang'),
                    
                // Menampilkan Produk beserta Varian dan SKU-nya
                TextColumn::make('product.name')
                    ->label('Item Barang')
                    ->formatStateUsing(function ($record) {
                        // Jika mutasi ini memiliki varian, gabungkan namanya
                        if ($record->product_variant_id && $record->productVariant) {
                            return $record->product->name . ' - ' . $record->productVariant->name;
                        }
                        return $record->product->name;
                    })
                    ->description(function ($record) {
                        // Tampilkan SKU Varian atau Kode Produk di baris bawah teks
                        return $record->product_variant_id 
                            ? 'SKU: ' . ($record->productVariant->sku ?? '-') 
                            : 'Kode: ' . $record->product->code;
                    })
                    ->searchable(),

                TextColumn::make('direction')
                    ->label('Mutasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'adjust' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),
                    
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->formatStateUsing(fn ($record) => ($record->direction === 'in' ? '+' : '-') . $record->quantity)
                    ->color(fn ($record) => $record->direction === 'in' ? 'success' : 'danger'),

                TextColumn::make('source_type')
                    ->label('Sumber / Dokumen')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $base = class_basename($state);
                        return match ($base) {
                            'SaleItem' => 'Penjualan POS',
                            'RepairItem' => 'Servis / Reparasi',
                            'StockOpname' => 'Stock Opname',
                            'purchase' => 'Penerimaan (PO)',
                            'stock_transfer_ship' => 'Transfer Keluar',
                            'stock_transfer_receive' => 'Transfer Masuk',
                            'RepairWarrantyClaim' => 'Klaim Garansi',
                            'Product' => 'Saldo Awal',
                            'manual_adjustment' => 'Penyesuaian Manual',
                            default => $base ?: '-',
                        };
                    })
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
                            'manual_adjustment' => 'warning',
                            default => 'secondary',
                        };
                    })
                    ->description(fn ($record) => $record->note ?: ($record->source_id ? 'Ref ID: #' . $record->source_id : null))
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('source_type', 'like', "%{$search}%")
                              ->orWhere('note', 'like', "%{$search}%");
                        });
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('direction')
                    ->label('Arah Mutasi')
                    ->options([
                        'in' => 'Masuk (IN)',
                        'out' => 'Keluar (OUT)',
                        'adjust' => 'Penyesuaian (ADJUST)',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('source_type')
                    ->label('Tipe Sumber')
                    ->options([
                        'App\Models\Tenant\SaleItem' => 'Penjualan POS',
                        'App\Models\Tenant\RepairItem' => 'Servis / Reparasi',
                        'App\Models\Tenant\StockOpname' => 'Stock Opname',
                        'purchase' => 'Penerimaan (PO)',
                        'stock_transfer_ship' => 'Transfer Keluar',
                        'stock_transfer_receive' => 'Transfer Masuk',
                        'App\Models\Tenant\RepairWarrantyClaim' => 'Klaim Garansi',
                    ]),
            ])
            ->recordActions([
                // EditAction::make(),
                // Menambahkan Action untuk Modal View Detail
                ViewAction::make()
                    ->label('Detail'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
