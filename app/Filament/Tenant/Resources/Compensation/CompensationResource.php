<?php

namespace App\Filament\Tenant\Resources\Compensation;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Compensation\Pages\CreateCompensation;
use App\Filament\Tenant\Resources\Compensation\Pages\EditCompensation;
use App\Filament\Tenant\Resources\Compensation\Pages\ListCompensation;
use App\Filament\Tenant\Resources\Compensation\RelationManagers\AssignmentsRelationManager;
use App\Filament\Tenant\Resources\Compensation\RelationManagers\ComponentsRelationManager;
use App\Filament\Tenant\Resources\Compensation\RelationManagers\PackageComponentsRelationManager;
use App\Filament\Tenant\Resources\Compensation\Schemas\CompensationForm;
use App\Filament\Tenant\Resources\Compensation\Tables\CompensationTable;
use App\Models\Tenant\CompensationPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CompensationResource extends Resource
{
    protected static ?string $model = CompensationPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Compensation';    
    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:compensation_package') ?? true;
    }


    public static function form(Schema $schema): Schema
    {
        return CompensationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompensationTable::configure($table);
    }


    public static function getRelations(): array
    {
        return [
            ComponentsRelationManager::class,
            AssignmentsRelationManager::class
            // PackageComponentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompensation::route('/'),
            'create' => CreateCompensation::route('/create'),
            'edit' => EditCompensation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
