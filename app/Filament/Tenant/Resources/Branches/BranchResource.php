<?php

namespace App\Filament\Tenant\Resources\Branches;

use App\Filament\Tenant\Resources\Branches\Pages\CreateBranch;
use App\Filament\Tenant\Resources\Branches\Pages\EditBranch;
use App\Filament\Tenant\Resources\Branches\Pages\ListBranches;
use App\Filament\Tenant\Resources\Branches\Schemas\BranchForm;
use App\Filament\Tenant\Resources\Branches\Tables\BranchesTable;
use App\Models\Tenant\Branch;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Branch Management';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
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
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }



    public static function canAccess(): bool
    {
        // return (bool) data_get(tenant()->features, 'client', false);



        if (!tenant()->hasFeatureCode(\App\Enums\TenantFeature::MAX_BRANCH)) {
            return false; // tenant tidak punya fitur → blokir resource
        }
        // Biarkan policy bekerja
        return Auth::user()?->can('viewAny:branch');
        // dd(tenant()->features);
        // return true;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
