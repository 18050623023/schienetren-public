<?php

namespace App\Filament\Tenant\Resources\Roles\Schemas;

use App\Models\Tenant\Permission;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use ReflectionClass;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Role Name')
                    ->columnSpanFull()
                    ->required(),

                Section::make('Permissions')
                    ->description('Pilih permission berdasarkan resource.')
                    ->headerActions([
                        \Filament\Actions\Action::make('selectAll')
                            ->label('Select All')
                            ->action(fn ($set) => self::updatePermissionsState($set)),
                        \Filament\Actions\Action::make('selectAllView')
                            ->label('Select All View')
                            ->action(fn ($set) => self::updatePermissionsState($set, 'view%')),
                        \Filament\Actions\Action::make('deselectAll')
                            ->label('Deselect All')
                            ->color('danger')
                            ->action(fn ($set) => self::updatePermissionsState($set, null, true)),
                    ])
                    ->schema(self::getDynamicPermissionSchema())
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }


    /**
     * Generate CheckboxList per Filament Resource
     */
    private static function getDynamicPermissionSchema(): array
    {
        $service = app(\App\Services\PermissionService::class);
        return $service->getDynamicPermissionSchema('tenant', \App\Models\Tenant\Permission::class);
    }

    /**
     * Update permissions state dynamically
     */
    private static function updatePermissionsState(callable $set, ?string $filter = null, bool $deselect = false): void
    {
        $permissions = Permission::query()
            ->when($filter, fn ($query) => $query->where('name', 'like', $filter))
            ->get();

        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode(':', $permission->name);
            if (count($parts) === 2) {
                if (!isset($grouped[$parts[1]])) {
                    $grouped[$parts[1]] = [];
                }
                if (!$deselect) {
                    $grouped[$parts[1]][] = $permission->name;
                }
            }
        }

        foreach ($grouped as $modelName => $perms) {
            $set("permissions.{$modelName}", $perms);
        }
    }
}
