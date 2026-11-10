<?php

namespace App\Filament\Tenant\Resources\FinanceAccounts;

use App\Filament\Tenant\Resources\FinanceAccounts\Pages\CreateFinanceAccount;
use App\Filament\Tenant\Resources\FinanceAccounts\Pages\EditFinanceAccount;
use App\Filament\Tenant\Resources\FinanceAccounts\Pages\ListFinanceAccounts;
use App\Filament\Tenant\Resources\FinanceAccounts\Schemas\FinanceAccountForm;
use App\Filament\Tenant\Resources\FinanceAccounts\Tables\FinanceAccountsTable;
use App\Models\Tenant\FinanceAccount;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinanceAccountResource extends Resource
{
    protected static ?string $model = FinanceAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Chart of Accounts';

    public static function canViewAny(): bool
    {
        return tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE);
    }

    public static function form(Schema $schema): Schema
    {
        return FinanceAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceAccountsTable::configure($table);
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
            'index' => ListFinanceAccounts::route('/'),
            'create' => CreateFinanceAccount::route('/create'),
            'edit' => EditFinanceAccount::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
