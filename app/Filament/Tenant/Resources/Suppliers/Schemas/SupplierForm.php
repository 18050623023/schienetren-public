<?php

namespace App\Filament\Tenant\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Supplier Name')
                    ->required(),
                TextInput::make('company')
                    ->required(),
                TextInput::make('whatsapp')
                    ->required()
                    ->prefix('+62')
                    ->tel(),
                TextArea::make('address')
                    ->required()
                    ->columnSpan(2),
                Textarea::make('description'),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
