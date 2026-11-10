<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments;

use App\Filament\Tenant\Resources\EmployeeAssignments\Pages\CreateEmployeeAssignment;
use App\Filament\Tenant\Resources\EmployeeAssignments\Pages\EditEmployeeAssignment;
use App\Filament\Tenant\Resources\EmployeeAssignments\Pages\ListEmployeeAssignments;
use App\Filament\Tenant\Resources\EmployeeAssignments\Pages\ViewEmployeeAssignment;
use App\Filament\Tenant\Resources\EmployeeAssignments\Schemas\EmployeeAssignmentForm;
use App\Filament\Tenant\Resources\EmployeeAssignments\Tables\EmployeeAssignmentsTable;
use App\Filament\Tenant\Resources\EmployeeAssignments\RelationManagers\CompensationPackagesRelationManager;
use App\Filament\Tenant\Resources\EmployeeAssignments\RelationManagers\RepairTechnicianCommissionsRelationManager;
use App\Models\Tenant\EmployeeAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmployeeAssignmentResource extends Resource
{
    protected static ?string $model = EmployeeAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Employee Assignment';

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    public static function getNavigationUrl(): string
    {
        $user = Auth::user();

        if ($user && !$user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            $assignment = EmployeeAssignment::whereHas('employee', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

            if ($assignment) {
                return static::getUrl('view', ['record' => $assignment->id]);
            }
        }

        return parent::getNavigationUrl();
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RepairTechnicianCommissionsRelationManager::class,
            CompensationPackagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeAssignments::route('/'),
            'view' => ViewEmployeeAssignment::route('/{record}'),
            // 'create' => CreateEmployeeAssignment::route('/create'),
            'edit' => EditEmployeeAssignment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user && !$user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $query->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class])
            ->orderByRaw('CASE WHEN end_date IS NULL OR end_date >= CURRENT_DATE THEN 1 ELSE 0 END DESC')
            ->orderBy('start_date', 'desc');

        return $query;
    }
}
