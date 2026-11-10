<?php

namespace App\Filament\Tenant\Resources\LeaveRequests;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\LeaveRequests\Pages\CreateLeaveRequest;
use App\Filament\Tenant\Resources\LeaveRequests\Pages\EditLeaveRequest;
use App\Filament\Tenant\Resources\LeaveRequests\Pages\ListLeaveRequests;
use App\Filament\Tenant\Resources\LeaveRequests\Schemas\LeaveRequestForm;
use App\Filament\Tenant\Resources\LeaveRequests\Tables\LeaveRequestsTable;
use App\Models\Tenant\LeaveRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Leave Request';

    protected static string|UnitEnum|null $navigationGroup = 'Cuti dan Izin';

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::LEAVE)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:leave_request') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return LeaveRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveRequestsTable::configure($table);
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
            'index' => ListLeaveRequests::route('/'),
            'create' => CreateLeaveRequest::route('/create'),
            'edit' => EditLeaveRequest::route('/{record}/edit'),
        ];
    }


    
}
