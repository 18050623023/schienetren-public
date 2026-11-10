<?php

namespace App\Filament\Tenant\Resources\LeaveTypes;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\LeaveTypes\Pages\CreateLeaveType;
use App\Filament\Tenant\Resources\LeaveTypes\Pages\EditLeaveType;
use App\Filament\Tenant\Resources\LeaveTypes\Pages\ListLeaveTypes;
use App\Filament\Tenant\Resources\LeaveTypes\Schemas\LeaveTypeForm;
use App\Filament\Tenant\Resources\LeaveTypes\Tables\LeaveTypesTable;
use App\Models\Tenant\LeaveType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Leave Type';

    protected static string|UnitEnum|null $navigationGroup = 'Cuti dan Izin';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::LEAVE)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:leave_type') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return LeaveTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveTypesTable::configure($table);
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
            'index' => ListLeaveTypes::route('/'),
            'create' => CreateLeaveType::route('/create'),
            'edit' => EditLeaveType::route('/{record}/edit'),
        ];
    }
}
