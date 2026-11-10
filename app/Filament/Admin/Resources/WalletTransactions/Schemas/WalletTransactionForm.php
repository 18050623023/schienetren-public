<?php

namespace App\Filament\Admin\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wallet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('balance_before')
                    ->required()
                    ->numeric(),
                TextInput::make('balance_after')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_id')
                    ->numeric(),
                TextInput::make('reference_type'),
                TextInput::make('description')
                    ->required(),
            ]);
    }
}
