<?php

namespace App\Filament\Tenant\Resources\FinanceJournals;

use App\Filament\Tenant\Resources\FinanceJournals\Pages\CreateFinanceJournal;
use App\Filament\Tenant\Resources\FinanceJournals\Pages\EditFinanceJournal;
use App\Filament\Tenant\Resources\FinanceJournals\Pages\ListFinanceJournals;
use App\Filament\Tenant\Resources\FinanceJournals\Schemas\FinanceJournalForm;
use App\Filament\Tenant\Resources\FinanceJournals\Tables\FinanceJournalsTable;
use App\Models\Tenant\FinanceJournal;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinanceJournalResource extends Resource
{
    protected static ?string $model = FinanceJournal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'reference_number';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    
    protected static ?string $navigationLabel = 'Jurnal Umum';

    public static function canViewAny(): bool
    {
        return tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE);
    }

    public static function form(Schema $schema): Schema
    {
        return FinanceJournalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceJournalsTable::configure($table);
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
            'index' => ListFinanceJournals::route('/'),
            'create' => CreateFinanceJournal::route('/create'),
            'edit' => EditFinanceJournal::route('/{record}/edit'),
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
