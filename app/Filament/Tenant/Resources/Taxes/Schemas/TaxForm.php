<?php

namespace App\Filament\Tenant\Resources\Taxes\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('rate')
                    ->numeric()
                    ->required(),
                Select::make('type')
                    ->options(app(DatabaseService::class)->getEnumValues('taxes', 'type')),
                Toggle::make('is_active')->default(true),
                Textarea::make('description')
            ]);
    }
}
