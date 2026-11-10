<?php

namespace App\Filament\Tenant\Resources\Products;

use App\Filament\Clusters\Stocks\StocksCluster;
use App\Filament\Tenant\Resources\Products\Pages\CreateProduct;
use App\Filament\Tenant\Resources\Products\Pages\EditProduct;
use App\Filament\Tenant\Resources\Products\Pages\ListProducts;
use App\Filament\Tenant\Resources\Products\Pages\ViewStock;
use App\Filament\Tenant\Resources\Products\Schemas\ProductForm;
use App\Filament\Tenant\Resources\Products\Tables\ProductsTable;
use App\Models\Tenant\Product;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Product Management';
    // protected static string|UnitEnum|null $navigationGroup = 'Products Setting';
    // protected static ?string $cluster = StocksCluster::class;

    public static function canCreate(): bool
    {
        return Product::checkProductLimit()->passed && parent::canCreate();
    }

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false;
    // }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['variants']);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
