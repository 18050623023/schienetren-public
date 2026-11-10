<?php

namespace App\Filament\Tenant\Resources\Holidays;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Holidays\Pages\CreateHoliday;
use App\Filament\Tenant\Resources\Holidays\Pages\EditHoliday;
use App\Filament\Tenant\Resources\Holidays\Pages\ListHolidays;
use App\Filament\Tenant\Resources\Holidays\Schemas\HolidayForm;
use App\Filament\Tenant\Resources\Holidays\Tables\HolidaysTable;
use App\Models\Tenant\Holiday;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Holiday';
    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::HOLIDAY)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:holiday') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return HolidayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HolidaysTable::configure($table);
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
            'index' => ListHolidays::route('/'),
            'create' => CreateHoliday::route('/create'),
            'edit' => EditHoliday::route('/{record}/edit'),
        ];
    }
}
