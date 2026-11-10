<?php

namespace App\Filament\Tenant\Resources\Categories\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name Category')
                    ->required(),
                Select::make('type')
                    ->options(app(DatabaseService::class)->getEnumValues('categories', 'type')),
                Textarea::make('description'),

            ]);
    }
}
