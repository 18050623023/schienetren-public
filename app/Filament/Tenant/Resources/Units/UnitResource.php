<?php

namespace App\Filament\Tenant\Resources\Units;

use App\Filament\Tenant\Resources\Units\Pages\CreateUnit;
use App\Filament\Tenant\Resources\Units\Pages\EditUnit;
use App\Filament\Tenant\Resources\Units\Pages\ListUnits;
use App\Filament\Tenant\Resources\Units\Schemas\UnitForm;
use App\Filament\Tenant\Resources\Units\Tables\UnitsTable;
use App\Models\Tenant\Unit;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Units Management';
    // protected static string|UnitEnum|null $navigationGroup = 'Products Setting';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return UnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitsTable::configure($table);
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
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }
}
