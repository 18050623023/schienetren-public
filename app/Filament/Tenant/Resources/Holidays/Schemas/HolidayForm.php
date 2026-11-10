<?php

namespace App\Filament\Tenant\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('is_generated'),
                Select::make('recurrence_type')
                    ->required()
                    ->label('Repeat Type')
                    // ->columnSpan(2)
                    ->reactive()
                    ->options([
                        'yearly' => 'Yearly',
                        'monthly' => 'Monthly',
                        'one_time' => 'One Time',
                    ]),

                Select::make('scope')
                    ->required()
                    ->reactive()
                    ->options([
                        'branch' => 'Branch',
                        'company' => 'Company',
                        'national' => 'National',
                    ])
                    ->default('national'),

                Toggle::make('is_active')
                    ->default(true)
                    ->inline(false),

                Section::make('Holiday Information')
                    ->columnSpan(3)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        Select::make('branch_id')
                            // ->columnSpan(2)
                            ->multiple()
                            ->preload()
                            ->relationship('branches', 'name')
                            ->visible(fn ($get) => $get('scope') === 'branch'),

                        DatePicker::make('date')
                            ->label('Holiday Date')
                            ->live()
                            ->visible(fn ($get) => $get('recurrence_type') === 'one_time')
                            ->required(fn ($get) => $get('recurrence_type') === 'one_time'),

                        TextInput::make('day')
                            ->numeric()
                            ->label('Day / Setiap tanggal')
                            ->visible(fn ($get) => $get('recurrence_type') === 'monthly')
                            ->required(fn ($get) => $get('recurrence_type') === 'monthly'),

                        Grid::make(2)
                            ->schema([
                                Select::make('month')
                                    ->visible(fn ($get) => $get('recurrence_type') === 'yearly')
                                    ->required(fn ($get) => $get('recurrence_type') === 'yearly')
                                    ->options([
                                        1 => 'Januari',
                                        2 => 'Februari',
                                        3 => 'Maret',
                                        4 => 'April',
                                        5 => 'Mei',
                                        6 => 'Juni',
                                        7 => 'Juli',
                                        8 => 'Agustus',
                                        9 => 'September',
                                        10 => 'Oktober',
                                        11 => 'November',
                                        12 => 'Desember',
                                    ])
                                    ->required(),

                                TextInput::make('day')
                                    ->label('Day / Tanggal')
                                    ->numeric()
                                    ->visible(fn ($get) => $get('recurrence_type') === 'yearly')
                                    ->required(fn ($get) => $get('recurrence_type') === 'yearly'),

                            ])
                            ->columnSpanFull(),

                        // TextInput::make('month')
                        //     ->live()
                        //     ->visible(fn ($get) => $get('recurrence_type') === 'yearly'),

                        // TextInput::make('day')
                        //     ->label('Day / Setiap tanggal')
                        //     ->live()
                        //     ->visible(fn ($get) => $get('recurrence_type') === 'yearly' || $get('recurrence_type') === 'monthly'),
                    ]),

            ]);
    }
}
