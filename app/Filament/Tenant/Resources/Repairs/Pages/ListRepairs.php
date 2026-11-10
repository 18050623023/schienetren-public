<?php

namespace App\Filament\Tenant\Resources\Repairs\Pages;

use App\Filament\Tenant\Resources\Repairs\Pages\Concerns\HandlesRepairItemSelection;
use App\Filament\Tenant\Resources\Repairs\RepairResource;
use App\Models\Tenant\Branch;
use App\Services\RepairService;
use App\Enums\RepairStatus;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListRepairs extends ListRecords
{
    use HandlesRepairItemSelection;

    public $repair;

    protected $queryString = [
        'repair' => ['except' => ''],
    ];

    public function mount(): void
    {
        parent::mount();

        if ($this->repair) {
            $decoded = \Vinkla\Hashids\Facades\Hashids::decode($this->repair);
            if (!empty($decoded)) {
                $id = (string) $decoded[0];
                $this->defaultTableAction = 'edit';
                $this->defaultTableActionRecord = $id;
            }
        }
    }

    protected static string $resource = RepairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->modalWidth('7xl')
                ->mutateFormDataUsing(function (array $data): array {
                    return \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mutateDataBeforeCreate($data);
                })
                ->after(function ($record, array $data) {
                    \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::afterSave($data, $record);
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->modifyQueryUsing(fn (Builder $query) => $query),
            'incoming' => Tab::make('Incoming')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::Incoming->value)),
            'checking' => Tab::make('Checking')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::Checking->value)),
            'waiting_confirmation' => Tab::make('Waiting Confirmation')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::WaitingConfirmation->value)),
            'in_service' => Tab::make('In Service')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::InService->value)),
            'partially_done' => Tab::make('Partially Done')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::PartialyDone->value)),
            'ready_to_pickup' => Tab::make('Ready to Deliver')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::ReadyToPickup->value)),
            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', RepairStatus::Delivered->value)),
        ];
    }



}
