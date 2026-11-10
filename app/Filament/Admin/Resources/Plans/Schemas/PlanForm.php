<?php

namespace App\Filament\Admin\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                \App\Services\TimeConversionService::makeDurationGrid('duration_in_days', 'Durasi'),
                \App\Services\TimeConversionService::makeDurationGrid('grace_period_days', 'Masa Tenggang'),
                Toggle::make('is_active')
                    ->required(),
                \Filament\Forms\Components\Repeater::make('planFeatures')
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->label('Features')
                    ->schema([
                        \Filament\Forms\Components\Select::make('feature_id')
                            ->relationship('feature', 'name')
                            ->label('Feature')
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        \Filament\Forms\Components\TextInput::make('value')
                            ->label('Value / Limit')
                            ->placeholder('e.g. 10, unlimited')
                            // ->required()
                            ,
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
