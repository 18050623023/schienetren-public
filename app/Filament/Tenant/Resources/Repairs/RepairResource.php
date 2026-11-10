<?php

namespace App\Filament\Tenant\Resources\Repairs;

use App\Filament\Tenant\Resources\Repairs\Pages\CreateRepair;
use App\Filament\Tenant\Resources\Repairs\Pages\EditRepair;
use App\Filament\Tenant\Resources\Repairs\Pages\ListRepairs;
use App\Filament\Tenant\Resources\Repairs\Schemas\RepairForm;
use App\Filament\Tenant\Resources\Repairs\Tables\RepairsTable;
use App\Models\Tenant\Repair;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RepairResource extends Resource
{
    protected static ?string $model = Repair::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Repair';

    protected static string|UnitEnum|null $navigationGroup = '';

    public static function canCreate(): bool
    {
        return Repair::checkMonthlyRepairLimit()->passed && parent::canCreate();
    }

    public static function form(Schema $schema): Schema
    {
        return RepairForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairsTable::configure($table);
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return \App\Filament\Tenant\Resources\Repairs\Schemas\RepairInfolist::configure($schema);
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairs::route('/'),
            // 'create' => CreateRepair::route('/create'),
            // 'edit' => EditRepair::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && ! $user->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {
            $branchId = $user->employees?->assignments()
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', today());
                })
                ->value('branch_id');

            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $branchId = session('current_branch_id');

            if (! empty($branchId) && $branchId != 0) {
                $query->where('branch_id', $branchId);
            }
        }

        $query->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);
        return $query;
    }
}
