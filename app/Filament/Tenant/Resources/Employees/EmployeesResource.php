<?php

namespace App\Filament\Tenant\Resources\Employees;

use App\Filament\Tenant\Resources\Employees\Pages\CreateEmployees;
use App\Filament\Tenant\Resources\Employees\Pages\EditEmployees;
use App\Filament\Tenant\Resources\Employees\Pages\ListEmployees;
use App\Filament\Tenant\Resources\Employees\RelationManagers\EmployeeAssignmentsRelationManager;
use App\Filament\Tenant\Resources\Employees\Schemas\EmployeesForm;
use App\Filament\Tenant\Resources\Employees\Tables\EmployeesTable;
use App\Models\Tenant\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmployeesResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Employee';

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    public static function form(Schema $schema): Schema
    {
        return EmployeesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // EmployeeRolesRelationManager::class,
            EmployeeAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployees::route('/create'),
            'edit' => EditEmployees::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user && !$user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            $query->where('user_id', $user->id);  
        }

        return $query;
    }
}
