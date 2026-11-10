<?php

namespace App\Filament\Tenant\Resources\DeviceModels;

use App\Filament\Tenant\Resources\DeviceModels\Pages\CreateDeviceModel;
use App\Filament\Tenant\Resources\DeviceModels\Pages\EditDeviceModel;
use App\Filament\Tenant\Resources\DeviceModels\Pages\ListDeviceModels;
use App\Filament\Tenant\Resources\DeviceModels\Schemas\DeviceModelForm;
use App\Filament\Tenant\Resources\DeviceModels\Tables\DeviceModelsTable;
use App\Models\Tenant\DeviceModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DeviceModelResource extends Resource
{
    protected static ?string $model = DeviceModel::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $recordTitleAttribute = 'name';

    // protected static ?string $navigationGroup = 'Master Data';

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function form(Schema $schema): Schema
    {
        return DeviceModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeviceModelsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeviceModels::route('/'),
            'create' => CreateDeviceModel::route('/create'),
            'edit' => EditDeviceModel::route('/{record}/edit'),
        ];
    }
}
