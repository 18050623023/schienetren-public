<?php

namespace App\Filament\Tenant\Resources\PaymentMethods\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(app(DatabaseService::class)->getEnumValues('payment_methods', 'type')),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
