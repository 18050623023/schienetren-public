<?php

namespace App\Filament\Tenant\Resources\SkillEmployees;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\SkillEmployees\Pages\CreateSkillEmployee;
use App\Filament\Tenant\Resources\SkillEmployees\Pages\EditSkillEmployee;
use App\Filament\Tenant\Resources\SkillEmployees\Pages\ListSkillEmployees;
use App\Filament\Tenant\Resources\SkillEmployees\RelationManagers\SkillSpecializationRelationManager;
use App\Filament\Tenant\Resources\SkillEmployees\Schemas\SkillEmployeeForm;
use App\Filament\Tenant\Resources\SkillEmployees\Tables\SkillEmployeesTable;
use App\Models\Tenant\SkillEmployee;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SkillEmployeeResource extends Resource
{
    protected static ?string $model = SkillEmployee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Employee Skill ';
    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:skill_employee') ?? true;
    }

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false;
    // }

    public static function form(Schema $schema): Schema
    {
        return SkillEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillEmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // SkillSpecializationRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkillEmployees::route('/'),
            'create' => CreateSkillEmployee::route('/create'),
            'edit' => EditSkillEmployee::route('/{record}/edit'),
        ];
    }
}
