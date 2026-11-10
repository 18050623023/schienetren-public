<?php

namespace App\Livewire\Components;

use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\ProductVariant;
use Filament\Actions\Action as ActionsAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class UniversalTableSelector extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public string $preset = '';
    public array $params = [];
    public string $statePath = '';
    public ?string $eventName = null;

    public function table(Table $table): Table
    {
        [$query, $columns] = $this->getPresetConfig($this->preset, $this->params);

        return $table
            ->query($query)
            ->columns($columns)
            ->recordActions([
                ActionsAction::make('select')
                    ->label('Pilih')
                    ->button()
                    ->disabled(function (Model $record) {
                        if ($record instanceof ProductVariant) {
                             if ($record->product?->type === 'service') {
                                 return false;
                             }
                             $branchId = $this->params['branch_id'] ?? $this->params['branchId'] ?? null;
                             $usedQuantities = $this->params['used_quantities'] ?? [];
                             $stock = $record->getStock($branchId);
                             $used = $usedQuantities[$record->id] ?? 0;
                             return ($stock - $used) <= 0;
                        }
                        return false;
                    })
                    ->action(fn (Model $record) => $this->selectRecord($record))
            ]);
    }

    protected function getPresetConfig(string $preset, array $params): array
    {
        $branchId = $params['branch_id'] ?? $params['branchId'] ?? null;
        $variantId = $params['variant_id'] ?? $params['variantId'] ?? null;
        $productId = $params['product_id'] ?? $params['productId'] ?? null;

        return match ($preset) {
            'product_variant' => [
                ProductVariant::query()
                    ->with(['product.brand', 'product.unit', 'stocks'])
                    ->whereHas('product', fn ($q) => $q->where('type', 'product'))
                    ->inStock($branchId),
                [
                    TextColumn::make('product.name')
                        ->label('Produk')
                        ->formatStateUsing(function ($state, ProductVariant $record) {
                            $productName = $state ?? '-';
                            $variantName = $record->name ?: ($record->product?->has_variant ? '-' : 'Default / Non-Varian');
                            return "{$productName} - {$variantName}";
                        })
                        ->description(fn (ProductVariant $record) => $record->product?->brand?->name)
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhereHas('product', function ($p) use ($search) {
                                      $p->where('name', 'like', "%{$search}%")
                                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$search}%"));
                                  });
                            });
                        })
                        ->sortable(),

                    TextColumn::make('sku')
                        ->label('SKU')
                        ->searchable()
                        ->copyable(),

                    TextColumn::make('product.has_sn')
                        ->label('Tipe')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state ? 'Serial Number' : 'Non-SN')
                        ->color(fn ($state) => $state ? 'warning' : 'gray'),

                    TextColumn::make('stock')
                        ->label('Stok Cabang Asal')
                        ->state(function (ProductVariant $record) use ($branchId, $params) {
                            $stock = $record->getStock($branchId);
                            $usedQuantities = $params['used_quantities'] ?? [];
                            $used = $usedQuantities[$record->id] ?? 0;
                            $remaining = max(0, $stock - $used);
                            return $remaining . ' ' . ($record->product?->unit?->name ?? 'pcs');
                        })
                        ->badge()
                        ->color(fn ($state) => str_starts_with((string)$state, '0') ? 'danger' : 'success'),
                    TextColumn::make('sale_price')
                        ->label('Harga Jual')
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                        ->sortable(),
                ]
            ],

            'service_variant' => [
                ProductVariant::query()
                    ->with(['product.brand', 'product.unit'])
                    ->whereHas('product', fn ($q) => $q->where('type', 'service')),
                [
                    TextColumn::make('product.name')
                        ->label('Nama Jasa')
                        ->formatStateUsing(function ($state, ProductVariant $record) {
                            $productName = $state ?? '-';
                            $variantName = $record->name ?: ($record->product?->has_variant ? '-' : 'Default / Non-Varian');
                            return "{$productName} - {$variantName}";
                        })
                        ->description(fn (ProductVariant $record) => $record->product?->brand?->name)
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhereHas('product', function ($p) use ($search) {
                                      $p->where('name', 'like', "%{$search}%")
                                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$search}%"));
                                  });
                            });
                        })
                        ->sortable(),
                        
                    TextColumn::make('sku')
                        ->label('SKU')
                        ->searchable()
                        ->copyable(),
                    TextColumn::make('sale_price')
                        ->label('Harga Jual')
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                        ->sortable(),
                ]
            ],

            'product_serial' => [
                ProductSerial::query()
                    ->where('status', 'available')
                    ->when($branchId, fn ($q, $b) => $q->where('branch_id', $b))
                    ->when($variantId, fn ($q, $v) => $q->where('product_variant_id', $v))
                    ->when(!$variantId && $productId, fn ($q) => 
                        $q->whereHas('productVariant', fn ($pv) => $pv->where('product_id', $productId))
                    ),
                [
                    TextColumn::make('serial_number')
                        ->label('Serial Number')
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('productVariant.name')
                        ->label('Varian')
                        ->searchable(),

                    TextColumn::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'available' => 'success',
                            'sold' => 'danger',
                            'defective' => 'warning',
                            default => 'gray',
                        }),
                ]
            ],

            default => throw new \InvalidArgumentException("Preset table selector [{$preset}] tidak terdaftar."),
        };
    }

    protected function selectRecord(Model $record)
    {
        $event = $this->eventName ?: match ($this->preset) {
            'product_variant' => 'product-variant-selected',
            'product_serial' => 'record-selected',
            default => 'record-selected',
        };

        $itemData = [];
        if ($record instanceof ProductVariant) {
            $p = $record->product;
            $brand = $p?->brand?->name ? "{$p->brand->name} " : '';
            $name = $brand . ($p?->name ?? '-') . ($record->name && $p?->has_variant ? " - {$record->name}" : '');
            $price = (float) ($record->sale_price ?? $p?->price ?? 0);
            $unit = $p?->warranty_unit?->value ?? $p?->warranty_unit;
            $warranty = $p?->warranty_value ? "{$p->warranty_value} {$unit}" : 'Tidak ada';

            $itemData = [
                'product_id' => $record->product_id,
                'product_variant_id' => $record->id,
                'name' => $name,
                'price' => $price,
                'warranty' => $warranty,
                'has_sn' => (bool) $p?->has_sn,
                'qty' => 1,
                'sn' => $p?->has_sn ? null : 'Non-SN',
                'product_serial_id' => null,
                'subtotal' => $price,
                'subtotal_item' => $price,
                'max_stock' => $p?->type === 'service' ? null : $record->getStock($this->params['branch_id'] ?? $this->params['branchId'] ?? null),
            ];
        } elseif ($record instanceof ProductSerial) {
            $itemData = [
                'id' => $record->id,
                'serial_number' => $record->serial_number,
                'product_variant_id' => $record->product_variant_id,
            ];
        }

        $this->dispatch(
            $event,
            recordId: $record->getKey(),
            statePath: $this->statePath,
            item: $itemData
        );
    }

    public function render()
    {
        // return view('livewire.components.base-modal-selector');
        return view('livewire.common.table-modal');
    }
}
