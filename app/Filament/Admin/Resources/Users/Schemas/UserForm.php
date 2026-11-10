<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),


                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignorable: fn($record) => $record),

                        DateTimePicker::make('email_verified_at')
                    ])
                    ->columns(2),

                Section::make('Password')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->label('Password Baru')
                            ->minLength(8)
                            ->maxLength(255)
                            // only persist (dehydrate) when user fill the field
                            ->dehydrated(fn($state) => filled($state))
                            // hash before saving
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->confirmed(),


                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Konfirmasi Password')
                            ->dehydrated(false),
                    ])
                    ->collapsed()
                    ->columns(2),

                Section::make('Role')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload(),
                    ])

                    ->columnSpanFull(),
            ]);
    }
}
