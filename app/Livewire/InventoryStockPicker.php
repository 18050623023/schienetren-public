<?php

namespace App\Livewire;

use App\Models\Tenant\Stock;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class InventoryStockPicker extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithSchemas;

    public string $targetStatePath = 'repair_items';
    public ?int $branchId = null;

    public function mount(string $targetStatePath = 'repair_items', ?int $branchId = null): void
    {
        $branchId = $branchId ?? session('current_branch_id');
        $this->branchId = filled($branchId) ? (int) $branchId : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Stock::query()
                    ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                    ->where('stock', '>', 0)
                    ->with(['product.brand', 'productVariant'])
            )
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable()
                    ->state(fn(Stock $record) => $record->productVariant?->sku ?? $record->product?->code ?? '-'),

                TextColumn::make('product.name')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn($state): ?string => $state)
                    ->state(function (Stock $record) {
                        $p = $record->product;
                        $v = $record->productVariant;
                        $brand = $p?->brand?->name ? "{$p->brand->name} " : '';

                        return $brand . ($p?->name ?? '-') . ($v && $p?->has_variant ? " - {$v->name}" : '');
                    }),

                TextColumn::make('productVariant.sale_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable()
                    ->state(fn(Stock $record) => (int) ($record->productVariant?->sale_price ?? $record->product?->price ?? 0)),

                TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('product.warranty_value')
                    ->label('Garansi')
                    ->state(function (Stock $record) {
                        $p = $record->product;
                        $unit = $p?->warranty_unit?->value ?? $p?->warranty_unit;

                        return $p?->warranty_value ? "{$p->warranty_value} {$unit}" : 'Tidak ada';
                    }),
            ])
            ->recordActions([
                Action::make('select')
                    ->label('Pilih')
                    ->icon('heroicon-m-shopping-cart')
                    ->color('primary')
                    ->button()
                    ->action(function (Stock $record) {
                        $p = $record->product;
                        $v = $record->productVariant;
                        $unit = $p?->warranty_unit?->value ?? $p?->warranty_unit;
                        $brand = $p?->brand?->name ? "{$p->brand->name} " : '';
                        $price = (int) ($v?->sale_price ?? $p?->price ?? 0);

                        $item = [
                            'product_id' => $p?->id,
                            'product_variant_id' => $v?->id,
                            'name' => $brand . ($p?->name ?? '-') . ($v && $p?->has_variant ? " - {$v->name}" : ''),
                            'price' => $price,
                            'warranty' => $p?->warranty_value ? "{$p->warranty_value} {$unit}" : 'Tidak ada',
                            'qty' => 1,
                            'sn' => $p?->has_sn ? '' : 'Non-SN',
                            'subtotal' => $price,
                            'subtotal_item' => $price,
                        ];

                        $this->dispatch('select-inventory-item', item: $item, targetStatePath: $this->targetStatePath);
                    }),
            ]);
    }

    public function render()
    {
        // return view('livewire.inventory-stock-picker');
        return view('livewire.common.table-modal');
    }
}
