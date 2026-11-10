<?php

namespace App\Filament\Tenant\Resources\WorkScheduleOverrides;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\WorkScheduleOverrides\Pages\CreateWorkScheduleOverride;
use App\Filament\Tenant\Resources\WorkScheduleOverrides\Pages\EditWorkScheduleOverride;
use App\Filament\Tenant\Resources\WorkScheduleOverrides\Pages\ListWorkScheduleOverrides;
use App\Filament\Tenant\Resources\WorkScheduleOverrides\Schemas\WorkScheduleOverrideForm;
use App\Filament\Tenant\Resources\WorkScheduleOverrides\Tables\WorkScheduleOverridesTable;
use App\Models\Tenant\WorkScheduleOverride;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WorkScheduleOverrideResource extends Resource
{
    protected static ?string $model = WorkScheduleOverride::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Work Schedule Override';

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:work_schedule_override') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return WorkScheduleOverrideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkScheduleOverridesTable::configure($table);
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
            'index' => ListWorkScheduleOverrides::route('/'),
            'create' => CreateWorkScheduleOverride::route('/create'),
            'edit' => EditWorkScheduleOverride::route('/{record}/edit'),
        ];
    }
}
