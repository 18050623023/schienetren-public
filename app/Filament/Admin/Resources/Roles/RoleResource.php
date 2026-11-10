<?php

namespace App\Filament\Admin\Resources\Roles;

use App\Filament\Admin\Resources\Roles\Pages\CreateRole;
use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Admin\Resources\Roles\Pages\ListRoles;
use App\Filament\Admin\Resources\Roles\Schemas\RoleForm;
use App\Filament\Admin\Resources\Roles\Tables\RolesTable;
use App\Models\Role;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Role Management';
    protected static string|UnitEnum|null $navigationGroup = 'Access Control Management';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    // public static function getRecordRouteBindingEloquentQuery(): Builder
    // {
    //     return parent::getRecordRouteBindingEloquentQuery()
    //         ->withoutGlobalScopes([
    //             SoftDeletingScope::class,
    //         ]);
    // }

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     // $this->record di sini sudah terdefinisi karena kita di page EditRecord
    //     $data['permissions'] = $this->record->permissions->pluck('name')->toArray();
    //     return $data;
    // }

    // // Pindahkan logic handleRecordUpdate juga ke sini (gunakan $this->record)
    // protected function handleRecordUpdate(array $data): Role
    // {
    //     // ... logic update dan sync
    //     $this->record->update(array_except($data, ['permissions']));
    //     $this->record->syncPermissions($data['permissions'] ?? []);

    //     return $this->record;
    // }
}
