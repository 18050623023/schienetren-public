<?php

namespace App\Filament\Tenant\Resources\HrPayrolls;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\HrPayrolls\Pages\EditHrPayroll;
use App\Filament\Tenant\Resources\HrPayrolls\Pages\ListHrPayrolls;
use App\Filament\Tenant\Resources\HrPayrolls\Pages\ViewHrPayroll;
use App\Filament\Tenant\Resources\HrPayrolls\Schemas\HrPayrollForm;
use App\Filament\Tenant\Resources\HrPayrolls\Tables\HrPayrollsTable;
use App\Models\Tenant\HrPayroll;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class HrPayrollResource extends Resource
{
    protected static ?string $model = HrPayroll::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'period';

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    protected static ?string $navigationLabel = 'Penggajian (Payroll)';

    public static function canAccess(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::PAYROLL)
            && Auth::user()?->hasRole(
                settingTenant('tenant_bypass_roles', ['owner'])
            );
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->when(session('current_branch_id'), fn (\Illuminate\Database\Eloquent\Builder $query, $branchId) => $query->where('branch_id', $branchId));
    }

    public static function form(Schema $schema): Schema
    {
        return HrPayrollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HrPayrollsTable::configure($table);
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
            'index' => ListHrPayrolls::route('/'),
            'view' => ViewHrPayroll::route('/{record}'),
            'edit' => EditHrPayroll::route('/{record}/edit'),
        ];
    }
}
