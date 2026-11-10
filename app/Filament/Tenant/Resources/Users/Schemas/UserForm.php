<?php

namespace App\Filament\Tenant\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

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
                            ->unique(ignorable: fn ($record) => $record),

                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->suffixAction(
                                Action::make('add_role')
                                    ->icon('heroicon-o-plus')
                                    ->url(route('filament.tenant.resources.roles.create'))
                                    ->openUrlInNewTab()
                                    ->tooltip('Add Role')
                            ),

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
                            ->dehydrated(fn ($state) => filled($state))
                            // hash before saving
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->confirmed(),


                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Konfirmasi Password')
                            ->dehydrated(false),
                    ])
                    ->collapsed()
                    ->columns(2),

                // Section::make('Cabang')
                //     ->schema([
                //         Select::make('branch')
                //         ->label('Cabang')
                //         ->relationship('branch', 'name')
                //         ->preload(),
                //     ]),
            ]);
    }
}
