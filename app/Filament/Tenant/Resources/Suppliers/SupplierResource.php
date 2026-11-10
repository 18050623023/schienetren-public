<?php

namespace App\Filament\Tenant\Resources\Suppliers;

use App\Filament\Tenant\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Tenant\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Tenant\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Tenant\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Tenant\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Tenant\Supplier;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Suppliers Management';
    protected static string|UnitEnum|null $navigationGroup = 'Purchasing';

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
