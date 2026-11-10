<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Stock;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;

class ProductSerialTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public $stockId;

    public function mount($stockId)
    {
        $this->stockId = $stockId;
    }

    public function table(Table $table): Table
    {
        $stock = Stock::query()->find($this->stockId);

        return $table
            ->query(
                ProductSerial::query()
                    ->where('product_variant_id', $stock?->product_variant_id)
                    ->where('branch_id', $stock?->branch_id)
            )
            ->columns([
                TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10, 25, 50, 'all']);
    }

    public function render()
    {
        // return view('livewire.tenant.product-serial-table');
        return view('livewire.common.table-modal');
    }
}
