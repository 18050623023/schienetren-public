<?php

namespace App\Filament\Tenant\Resources\CompensationComponents\Schemas;

use App\Models\Tenant\CompensationComponent;
use App\Services\DatabaseService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class CompensationComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->live()
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                    )
                    ->afterStateUpdated(function ($state, callable $set) {
                        $exists = CompensationComponent::where('name', $state)->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Nama sudah digunakan')
                                ->danger()
                                ->send();

                            // Kosongkan field
                            $set('name', '');
                        }
                    }),
                Select::make('type')
                    ->options(app(DatabaseService::class)->getEnumValues('compensation_components', 'type')),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
