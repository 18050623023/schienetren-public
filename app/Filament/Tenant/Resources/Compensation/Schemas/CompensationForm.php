<?php

namespace App\Filament\Tenant\Resources\Compensation\Schemas;

use App\Services\DatabaseService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CompensationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('name')
                //     ->required(),
                 Grid::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columnSpan(2)
                            ,

                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->label('Save Changes'),
                            ])
                            ->visibleOn(['edit'])
                            ->columnSpan(1)
                            ->extraAttributes(['style' => 'flex:1;display:flex;justify-content:flex-end'])
                            ,
                    ]),

            ]);
    }
}
