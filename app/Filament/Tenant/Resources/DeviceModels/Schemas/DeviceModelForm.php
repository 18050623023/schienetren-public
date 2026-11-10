<?php

namespace App\Filament\Tenant\Resources\DeviceModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeviceModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Select::make('device_type_id')
                    ->relationship('deviceType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
