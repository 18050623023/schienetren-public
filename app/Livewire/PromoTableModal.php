<?php

namespace App\Livewire;

use App\Models\Promo;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;

class PromoTableModal extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public float $subtotal = 0;
    public string $itemType = 'all'; // plan, addon, topup

    public function mount(float $subtotal, string $itemType)
    {
        $this->subtotal = $subtotal;
        $this->itemType = $itemType;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Promo::query()
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->where('target', 'all')
                              ->orWhere('target', $this->itemType);
                    })
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Promo')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->description(fn (Promo $record) => $this->getPromoDescription($record)),
                TextColumn::make('value')
                    ->label('Diskon')
                    ->formatStateUsing(function (string $state, Promo $record) {
                        if ($record->type === 'percentage') {
                            return (float) $state . '%';
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('applyPromo')
                    ->label('Terapkan')
                    ->button()
                    ->color(fn (Promo $record) => $record->isValidFor($this->subtotal, $this->itemType)[0] ? 'primary' : 'gray')
                    ->disabled(fn (Promo $record) => !$record->isValidFor($this->subtotal, $this->itemType)[0])
                    ->tooltip(function (Promo $record) {
                        $validation = $record->isValidFor($this->subtotal, $this->itemType);
                        if (!$validation[0]) {
                            return $validation[1]; // Tampilkan alasan kenapa tidak valid
                        }
                        return 'Gunakan Promo ini';
                    })
                    ->action(function (Promo $record) {
                        // Dispatch event to parent (Checkout Page)
                        $this->dispatch('promo-selected', promo_code: $record->code);
                        
                    }),
            ])
            ->emptyStateHeading('Tidak ada promo tersedia');
    }

    private function getPromoDescription(Promo $record): string
    {
        $desc = [];
        if ($record->min_spend > 0) {
            $desc[] = 'Min. Trx Rp' . number_format($record->min_spend, 0, ',', '.');
        }
        if ($record->type === 'percentage' && $record->max_discount > 0) {
            $desc[] = 'Maks. Rp' . number_format($record->max_discount, 0, ',', '.');
        }
        if ($record->end_date) {
            $desc[] = 'Berlaku s/d ' . $record->end_date->format('d M Y');
        }

        return implode(' • ', $desc);
    }

    public function render()
    {
        // return view('livewire.promo-table-modal');
        return view('livewire.common.table-modal');
    }
}
