<?php

namespace App\Filament\Tenant\Resources\Categories;

use App\Filament\Tenant\Resources\Categories\Pages\CreateCategory;
use App\Filament\Tenant\Resources\Categories\Pages\EditCategory;
use App\Filament\Tenant\Resources\Categories\Pages\ListCategories;
use App\Filament\Tenant\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Tenant\Resources\Categories\Tables\CategoriesTable;
use App\Models\Tenant\Category;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Category Management';
    // protected static string|UnitEnum|null $navigationGroup = 'Products Setting';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
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
