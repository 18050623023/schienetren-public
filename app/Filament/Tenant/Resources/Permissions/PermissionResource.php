<?php

namespace App\Filament\Tenant\Resources\Permissions;

use App\Filament\Tenant\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Tenant\Resources\Permissions\Pages\EditPermission;
use App\Filament\Tenant\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Tenant\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Tenant\Resources\Permissions\Tables\PermissionsTable;
use App\Models\Tenant\Permission;
use App\Models\Tenant\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Permission Management';
    protected static string|UnitEnum|null $navigationGroup = 'Access Control Management';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
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
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }

    // public static function canViewAny(User $user): bool
    // {
    //     return $user->can('viewAny', Permission::class);
    // }

}
