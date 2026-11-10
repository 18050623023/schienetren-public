<?php

namespace App\Filament\Tenant\Resources\Brands\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;


class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(app(DatabaseService::class)->getEnumValues('brands', 'type')),
                Textarea::make('description'),
            ]);
    }
}
