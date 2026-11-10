<?php

namespace App\Filament\Tenant\Resources\StockMovements;

use App\Filament\Tenant\Resources\StockMovements\Pages\CreateStockMovement;
use App\Filament\Tenant\Resources\StockMovements\Pages\EditStockMovement;
use App\Filament\Tenant\Resources\StockMovements\Pages\ListStockMovements;
use App\Filament\Tenant\Resources\StockMovements\Schemas\StockMovementForm;
use App\Filament\Tenant\Resources\StockMovements\Tables\StockMovementsTable;
use App\Models\Tenant\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Tenant\Resources\StockMovements\RelationManagers;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'Stock Movement';
    protected static ?string $navigationLabel = 'Kartu Stok (Mutasi)';    
    protected static string|UnitEnum|null $navigationGroup = 'Stock';


    // MATIKAN SEMUA AKSES MANIPULASI DATA
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function infolist(Schema $schema): Schema
    {
        return StockMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductSerialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
            // 'create' => CreateStockMovement::route('/create'),
            // 'edit' => EditStockMovement::route('/{record}/edit'),
        ];
    }
}
