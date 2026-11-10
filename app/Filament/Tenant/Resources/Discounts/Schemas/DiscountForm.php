<?php

namespace App\Filament\Tenant\Resources\Discounts\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->required()
                    ->options(app(DatabaseService::class)->getEnumValues('discounts', 'type')),
                TextInput::make('value')
                    ->numeric()
                    ->required(),
                TextInput::make('min_transaction')
                    ->numeric()
                    ->required(),
                TextInput::make('max_discount')
                    ->numeric()
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
