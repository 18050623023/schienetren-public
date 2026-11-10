<?php

namespace App\Livewire\Components;

use Filament\Actions\Action as ActionsAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
// use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

abstract class BaseModalSelector extends Component implements HasForms, HasTable, HasActions
{


    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public string $statePath;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions([
                ActionsAction::make('select')
                    ->label('Pilih')
                    ->button()
                    ->action(fn(Model $record) => $this->selectRecord($record))
            ]);
    }

    abstract protected function getTableQuery(): Builder;
    abstract protected function getTableColumns(): array;

    protected function selectRecord(Model $record)
    {
        // dd($record->getKey(), $this->statePath);
        $this->dispatch(
            'record-selected',
            recordId: $record->getKey(),
            statePath: $this->statePath
        );
    }

    public function render()
    {
        // return view('livewire.components.base-modal-selector');
        return view('livewire.common.table-modal');
    }
}
