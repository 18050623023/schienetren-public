<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\Schemas;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Branches\BranchResource;
use App\Filament\Tenant\Resources\Employees\EmployeesResource;
use App\Filament\Tenant\Resources\Roles\RoleResource;
use App\Filament\Tenant\Resources\WorkSchedules\WorkScheduleResource;
use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\WorkSchedule;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class EmployeeAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('employee_id')
                    ->required()
                    ->live()
                    ->dehydrated()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                    ->disabled(function ($livewire) {
                        return $livewire instanceof RelationManager;
                    })
                    ->default(function ($livewire) {
                        if ($livewire instanceof RelationManager) {
                            return $livewire->getOwnerRecord()->id;
                        }

                        return null;
                    })
                // ->relationship('employee', 'id')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query, Get $get) {

                            $branchId = $get('branch_id');

                            $query->whereDoesntHave('assignments', function ($query) use ($branchId) {
                                $query
                                    ->where('branch_id', $branchId)
                                    ->whereDate('end_date', '>=', now())
                                    ->whereNull('end_date');
                            });

                        }
                    )
                    ->suffixAction(
                        Action::make('add_employee')
                            ->icon('heroicon-o-plus')
                            ->url(EmployeesResource::getUrl('create'))
                            ->openUrlInNewTab()
                            ->tooltip('Add Employee')
                    ),
                Select::make('branch_id')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    // ->relationship('branch', 'name')
                    ->relationship(
                        name: 'branch',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query, Get $get) {

                            $employeeId = $get('employee_id');

                            $query->whereDoesntHave('employeeAssignments', function ($query) use ($employeeId) {
                                $query
                                    ->where('employee_id', $employeeId)
                                    ->whereDate('end_date', '>=', now())
                                    ->whereNull('end_date');
                            });

                        }
                    )
                    ->suffixAction(
                        Action::make('add_branch')
                            ->icon('heroicon-o-plus')
                            ->url(BranchResource::getUrl('create'))
                            ->openUrlInNewTab()
                            ->tooltip('Add Branch')
                    ),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->required()
                    ->suffixAction(
                        Action::make('add_Role')
                            ->icon('heroicon-o-plus')
                            ->url(RoleResource::getUrl('create'))
                            ->openUrlInNewTab()
                            ->tooltip('Add Role')
                    ),
                Select::make('work_schedule_id')
                    ->searchable()
                    ->relationship('workSchedule', 'name')
                    ->preload()
                    ->required()
                    ->default(fn () => WorkSchedule::getDefaultSchedule()?->id)
                    ->disabled(fn () => !tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT))
                    ->dehydrated()
                    ->helperText(fn () => !tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)
                        ? 'Menggunakan Jadwal Standar (Senin–Sabtu, 08:00–17:00). Fitur Shift Management nonaktif.'
                        : null)
                    ->suffixAction(
                        Action::make('add_WorkSchedule')
                            ->icon('heroicon-o-plus')
                            ->url(WorkScheduleResource::getUrl('create'))
                            ->openUrlInNewTab()
                            ->tooltip('Add Work Schedule')
                            ->visible(fn () => tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT))
                    ),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->helperText('Kosongkan jika karyawan tetap.'),
            ]);
    }
}
