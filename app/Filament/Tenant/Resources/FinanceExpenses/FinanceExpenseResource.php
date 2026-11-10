<?php

namespace App\Filament\Tenant\Resources\FinanceExpenses;

use App\Filament\Tenant\Resources\FinanceExpenses\Pages\CreateFinanceExpense;
use App\Filament\Tenant\Resources\FinanceExpenses\Pages\EditFinanceExpense;
use App\Filament\Tenant\Resources\FinanceExpenses\Pages\ListFinanceExpenses;
use App\Filament\Tenant\Resources\FinanceExpenses\Schemas\FinanceExpenseForm;
use App\Filament\Tenant\Resources\FinanceExpenses\Tables\FinanceExpensesTable;
use App\Models\Tenant\FinanceExpense;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinanceExpenseResource extends Resource
{
    protected static ?string $model = FinanceExpense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'reference_number';
    
    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    
    protected static ?string $navigationLabel = 'Pengeluaran';

    public static function form(Schema $schema): Schema
    {
        return FinanceExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceExpensesTable::configure($table);
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
            'index' => ListFinanceExpenses::route('/'),
            'create' => CreateFinanceExpense::route('/create'),
            'edit' => EditFinanceExpense::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(session('current_branch_id'), fn (Builder $query, $branchId) => $query->where('branch_id', $branchId));
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
