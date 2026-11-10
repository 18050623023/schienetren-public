<?php

namespace App\Filament\Tenant\Resources\Clients\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

use function Laravel\Prompts\grid;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('phone')
                        ->required()
                        ->tel()
                        ->placeholder('8xxxxxxxxxx')
                        ->telRegex('/^8[1-9][0-9]{6,10}$/')
                        ->prefix('+62')
                        ->suffixAction(
                            Action::make('copy')
                                ->icon('heroicon-o-clipboard')
                                ->action(fn (Set $set, Get $get) => $set('whatsapp', $get('phone')))
                                ->tooltip('Copy to WhatsApp')
                        ),
                    TextInput::make('whatsapp')
                        ->required()
                        ->prefix('+62')
                        ->tel(),
                    Textarea::make('address')
                        ->columnSpanFull()
                        ->required(),

            ])
        ];
    }
}
