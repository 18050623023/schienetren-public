<?php

namespace App\Filament\Tenant\Resources\CompensationComponents;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\CompensationComponents\Pages\CreateCompensationComponent;
use App\Filament\Tenant\Resources\CompensationComponents\Pages\EditCompensationComponent;
use App\Filament\Tenant\Resources\CompensationComponents\Pages\ListCompensationComponents;
use App\Filament\Tenant\Resources\CompensationComponents\Schemas\CompensationComponentForm;
use App\Filament\Tenant\Resources\CompensationComponents\Tables\CompensationComponentsTable;
use App\Models\Tenant\CompensationComponent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompensationComponentResource extends Resource
{
    protected static ?string $model = CompensationComponent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $recordTitleAttribute = 'Compensation Component';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::PAYROLL)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:compensation_component') ?? true;
    }


    public static function form(Schema $schema): Schema
    {
        return CompensationComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompensationComponentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompensationComponents::route('/'),
            'create' => CreateCompensationComponent::route('/create'),
            'edit' => EditCompensationComponent::route('/{record}/edit'),
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
