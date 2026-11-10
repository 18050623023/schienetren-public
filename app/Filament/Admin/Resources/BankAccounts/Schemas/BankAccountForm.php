<?php

namespace App\Filament\Admin\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->label('Bank Name')
                    ->required()
                    ->maxLength(50),
                TextInput::make('account_holder')
                    ->label('Account Holder')
                    ->required()
                    ->maxLength(100),
                TextInput::make('account_number')
                    ->label('Account Number')
                    ->numeric()
                    ->required()
                    ->maxLength(50),
                Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true),
                RichEditor::make('instructions')
                    ->label('Instructions')
                    ->columnSpanFull()
                    ->required(),    
            ]);
    }
}
