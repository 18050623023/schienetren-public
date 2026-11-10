<?php

namespace App\Filament\Tenant\Widgets;

use App\Enums\RepairDeviceStatus;
use App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic;
use App\Filament\Tenant\Resources\Repairs\Schemas\RepairForm;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairDevice;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompletedRepairsTableWidget extends TableWidget
{
    protected static ?string $heading = 'Riwayat Perangkat Selesai & Batal';

    protected string $view = 'filament.tenant.widgets.completed-repairs-table-widget';

    public string $statusTab = 'all';

    public function getDeviceCount(string $tab = 'all'): int
    {
        $query = $this->getBaseQuery();

        if ($tab === 'done') {
            $query->where('status', RepairDeviceStatus::Done->value);
        } elseif ($tab === 'not_possible') {
            $query->where('status', RepairDeviceStatus::NotPossible->value);
        } elseif ($tab === 'cancel') {
            $query->where('status', RepairDeviceStatus::Cancel->value);
        }

        return $query->count();
    }

    protected function getBaseQuery(): Builder
    {
        $query = RepairDevice::query()
            ->whereIn('status', [
                RepairDeviceStatus::Done->value,
                RepairDeviceStatus::NotPossible->value,
                RepairDeviceStatus::Cancel->value,
            ]);

        if ($branchId = session('current_branch_id')) {
            $query->whereHas('repair', fn ($q) => $q->where('branch_id', $branchId));
        }

        $user = Auth::user();
        if (!$user || $user->hasRole(array_merge((array) settingTenant('tenant_bypass_roles', ['owner'])))) {
            return $query;
        }

        if ($user->employees) {
            $userAssignmentIds = $user->employees->assignments->pluck('id')->toArray();
            $query->whereHas('technicians', function ($qTech) use ($userAssignmentIds) {
                $qTech->whereIn('employee_assignment_id', $userAssignmentIds);
            });
        }

        return $query;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = $this->getBaseQuery();

                if ($this->statusTab === 'done') {
                    $query->where('status', RepairDeviceStatus::Done->value);
                } elseif ($this->statusTab === 'not_possible') {
                    $query->where('status', RepairDeviceStatus::NotPossible->value);
                } elseif ($this->statusTab === 'cancel') {
                    $query->where('status', RepairDeviceStatus::Cancel->value);
                }

                return $query->latest('updated_at');
            })
            ->columns([
                TextColumn::make('repair.code')
                    ->label('No. Reparasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('repair.client.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand_model')
                    ->label('Perangkat / Model')
                    ->state(function (RepairDevice $record): string {
                        $deviceType = $record->deviceType?->name ? $record->deviceType->name . ' - ' : '';
                        $brand = $record->brand?->name ?? '';
                        return trim("{$deviceType}{$brand} {$record->model}");
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('technicians')
                    ->label('Teknisi')
                    ->state(function (RepairDevice $record): string {
                        $names = $record->technicians
                            ->map(fn($t) => $t->employeeAssignment?->employee?->name)
                            ->filter()
                            ->unique()
                            ->toArray();

                        return !empty($names) ? implode(', ', $names) : '-';
                    }),

                TextColumn::make('updated_at')
                    ->label('Tanggal Selesai/Update')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->schema(function (Schema $schema, ViewAction $action, ?Model $record): Schema {
                        $repair = $record instanceof RepairDevice ? $record->repair : $record;

                        if ($repair) {
                            $action->record($repair);
                            $schema->model($repair);
                        }

                        return RepairForm::configure($schema);
                    })
                    ->fillForm(function (?Model $record): array {
                        $repair = $record instanceof RepairDevice ? $record->repair : $record;
                        if (! $repair) {
                            return [];
                        }

                        $data = $repair->attributesToArray();

                        return RepairFormLogic::mutateDataBeforeFill($data, $repair);
                    })
                    ->mutateRecordDataUsing(function (array $data, ?Model $record): array {
                        $repair = $record instanceof RepairDevice ? $record->repair : $record;

                        return $repair ? RepairFormLogic::mutateDataBeforeFill($data, $repair) : $data;
                    }),
            ])
            ->defaultPaginationPageOption(10);
    }
}
