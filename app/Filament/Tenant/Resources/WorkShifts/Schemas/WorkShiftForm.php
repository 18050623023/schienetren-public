<?php

namespace App\Filament\Tenant\Resources\WorkShifts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shift Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('break_minutes')
                            ->numeric()
                            ->suffix('Menit')
                            ->required(),
                        TextInput::make('late_tolerance')
                            ->numeric()
                            ->suffix('Menit'),
                        TextInput::make('overtime_tolerance')
                            ->numeric()
                            ->suffix('Menit'),
                    ]),
                Section::make('Shift Time')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('start_time')
                            ->required(),
                        TimePicker::make('end_time')
                            ->required(),
                    ]),
            ]);
    }
}
