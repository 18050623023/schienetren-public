<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use App\Models\Permission;
use App\Models\Role;
use Filament\Facades\Filament;
// use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use Termwind\Components\Raw;
use Livewire\Component as Livewire;

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
                    // ->columnSpanFull()
                    ->schema(self::getDynamicPermissionSchema())
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

            ]);

    }


    /**
     * Generate CheckboxList per Filament Resource
     */
    private static function getDynamicPermissionSchema(): array
    {
        $service = app(\App\Services\PermissionService::class);
        return $service->getDynamicPermissionSchema('admin', \Spatie\Permission\Models\Permission::class);
    }
}
