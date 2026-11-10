<?php

namespace App\Filament\Admin\Resources\Features\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('code')
                    ->options(\App\Enums\TenantFeature::class)
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(app(DatabaseService::class)->getEnumValues('features', 'type'))
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
