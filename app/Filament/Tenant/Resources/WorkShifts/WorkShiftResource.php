<?php

namespace App\Filament\Tenant\Resources\WorkShifts;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\WorkShifts\Pages\CreateWorkShift;
use App\Filament\Tenant\Resources\WorkShifts\Pages\EditWorkShift;
use App\Filament\Tenant\Resources\WorkShifts\Pages\ListWorkShifts;
use App\Filament\Tenant\Resources\WorkShifts\Schemas\WorkShiftForm;
use App\Filament\Tenant\Resources\WorkShifts\Tables\WorkShiftsTable;
use App\Models\Tenant\WorkShift;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkShiftResource extends Resource
{
    protected static ?string $model = WorkShift::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Work Shift';
    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SHIFT_MANAGEMENT)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:work_shift') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return WorkShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkShiftsTable::configure($table);
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
            'index' => ListWorkShifts::route('/'),
            'create' => CreateWorkShift::route('/create'),
            'edit' => EditWorkShift::route('/{record}/edit'),
        ];
    }
}
