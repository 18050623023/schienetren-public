<?php

namespace App\Filament\Tenant\Resources\DeviceTypes\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

class DeviceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Actions::make([
                    Action::make('save')
                        ->submit('save')
                        ->label('Save Changes'),
                    ])
                    ->visibleOn(['edit'])
                    ->columnSpan(1)
                    ->extraAttributes(['style' => 'flex:1;display:flex;justify-content:flex-end'])
                    ,
            ]);
    }
}
