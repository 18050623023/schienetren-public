<?php

namespace App\Filament\Admin\Resources\PaymentConfirmations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PaymentConfirmationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    // ->columnSpanFull()
                    ->preload(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Section::make('Payment Information')
                    ->columns(2)
                    ->schema([
                        Select::make('bank_account_id')
                            ->relationship('bankAccount', 'account_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('sender_bank_name')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->sender_bank_name . ' - ' . $record->sender_account_name : $state),
                        TextInput::make('transfer_amount')
                            ->numeric()
                            ->prefix('Rp'),
                    ]),

                Section::make('Approval Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reviewed_by')
                            ->numeric(),
                        DateTimePicker::make('reviewed_at'),
                        Textarea::make('rejection_reason')
                            ->columnSpanFull(),
                    ]),

                FileUpload::make('proof_file_path')
                    ->directory(fn($record) => $record?->proof_file_path)
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->columnSpanFull(),

                    
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
