<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\StockOpnameItem;
use App\Models\Tenant\StockOpnameItemSerial;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Actions\Concerns\InteractsWithActions;

class StockOpnameItemSerialTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public $itemId;

    public function mount($itemId)
    {
        $this->itemId = $itemId;
    }

    public function table(Table $table): Table
    {
        $query = StockOpnameItemSerial::query()
            ->where('stock_opname_item_id', $this->itemId);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'ok', 'available' => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
            ])
            ->paginated([5, 10, 25, 50, 'all']);
    }

    public function render()
    {
        // return view('livewire.tenant.stock-opname-item-serial-table');
        return view('livewire.common.table-modal');
    }
}
