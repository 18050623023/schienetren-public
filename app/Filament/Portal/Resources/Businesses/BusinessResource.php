<?php

namespace App\Filament\Portal\Resources\Businesses;

use App\Filament\Portal\Resources\Businesses\Pages\CreateBusiness;
use App\Filament\Portal\Resources\Businesses\Pages\EditBusiness;
use App\Filament\Portal\Resources\Businesses\Pages\ListBusinesses;
use App\Filament\Portal\Resources\Businesses\Schemas\BusinessForm;
use App\Filament\Portal\Resources\Businesses\Tables\BusinessesTable;
use App\Models\Business;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BusinessResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'business_name';
    protected static ?string $modelLabel = 'Bisnis Saya';
    protected static ?string $pluralModelLabel = 'Bisnis Saya';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Schema $schema): Schema
    {
        return BusinessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessesTable::configure($table);
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
            'index' => ListBusinesses::route('/'),
            'create' => CreateBusiness::route('/create'),
            // 'edit' => EditBusiness::route('/{record}/edit'),
        ];
    }


    public static function canView(Model $record): bool
    {
        return $record->user_id === Auth::id();
    }

    public static function canEdit(Model $record): bool
    {
        return $record->user_id === Auth::id();
        // return true;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->user_id === Auth::id();
    }
}
