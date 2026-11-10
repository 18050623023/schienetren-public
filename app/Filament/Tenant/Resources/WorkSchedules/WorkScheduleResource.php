<?php

namespace App\Filament\Tenant\Resources\WorkSchedules;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\WorkSchedules\Pages\CreateWorkSchedule;
use App\Filament\Tenant\Resources\WorkSchedules\Pages\EditWorkSchedule;
use App\Filament\Tenant\Resources\WorkSchedules\Pages\ListWorkSchedules;
use App\Filament\Tenant\Resources\WorkSchedules\Schemas\WorkScheduleForm;
use App\Filament\Tenant\Resources\WorkSchedules\Tables\WorkSchedulesTable;
use App\Models\Tenant\WorkSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Work Schedule';

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:work_schedule') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return WorkScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkSchedulesTable::configure($table);
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
            'index' => ListWorkSchedules::route('/'),
            // 'view' => ViewWorkSchedule::route('/{record}'),
            'create' => CreateWorkSchedule::route('/create'),
            'edit' => EditWorkSchedule::route('/{record}/edit'),
        ];
    }

    // public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->withoutGlobalScopes([
    //             \Illuminate\Database\Eloquent\SoftDeletingScope::class,
    //         ]);
    // }
}
