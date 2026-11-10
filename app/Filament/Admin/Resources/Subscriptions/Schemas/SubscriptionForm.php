<?php

namespace App\Filament\Admin\Resources\Subscriptions\Schemas;

use App\Services\DatabaseService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->required()
                    ->preload()
                    ->relationship('user', 'name')
                    ->searchable(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required()
                    ->preload(),
                Select::make('status')
                    ->required()
                    ->options(app(DatabaseService::class)->getEnumValues('subscriptions', 'status'))
                    ->default('pending'),

                Toggle::make('auto_renew')
                    ->required(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                DateTimePicker::make('grace_period_ends_at'),
            ]);
    }
}
