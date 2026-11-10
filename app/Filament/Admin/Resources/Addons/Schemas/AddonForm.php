<?php

namespace App\Filament\Admin\Resources\Addons\Schemas;

use App\Services\TimeConversionService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AddonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('Rp'),
                TimeConversionService::makeDurationGrid('duration_in_days', 'Durasi'),
                TimeConversionService::makeDurationGrid('grace_period_days', 'Masa Tenggang'),
                Toggle::make('is_active')
                    ->required(),
                Repeater::make('addonFeatures')
                    ->relationship()
                    ->label('Features')
                    ->schema([
                        Select::make('feature_id')
                            ->relationship('feature', 'name')
                            ->label('Feature')
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('value')
                            ->label('Value / Limit')
                            ->placeholder('e.g. 10, unlimited')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
