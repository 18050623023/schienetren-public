<?php

namespace App\Filament\Tenant\Resources\DeviceTypes;

use App\Filament\Tenant\Resources\DeviceTypes\Pages\CreateDeviceType;
use App\Filament\Tenant\Resources\DeviceTypes\Pages\EditDeviceType;
use App\Filament\Tenant\Resources\DeviceTypes\Pages\ListDeviceTypes;
use App\Filament\Tenant\Resources\DeviceTypes\RelationManagers\ChecklistsRelationManager;
use App\Filament\Tenant\Resources\DeviceTypes\Schemas\DeviceTypeForm;
use App\Filament\Tenant\Resources\DeviceTypes\Tables\DeviceTypesTable;
use App\Models\Tenant\DeviceType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeviceTypeResource extends Resource
{
    protected static ?string $model = DeviceType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Device Manajement';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return DeviceTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeviceTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ChecklistsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeviceTypes::route('/'),
            'create' => CreateDeviceType::route('/create'),
            'edit' => EditDeviceType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
