<?php

namespace App\Filament\Tenant\Resources\WorkSchedules\Schemas;

use Dom\Text;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                Radio::make('is_active')
                    ->label('Apakah Jadwal ini aktif?')
                    ->boolean()
                    ->inline()
                    ->required()
                    ->default(true),

                Repeater::make('days')
                    ->relationship('days')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([

                        // TextInput::make('day')
                        //     ->label('Nama Hari')
                        //     ->required(),

                        // TextInput::make('day_of_week')
                        //     ->numeric()
                        //     ->placeholder('1 = Senin, 2 = Selasa, dst')
                        //     ->required(),

                        Select::make('day_of_week')
                            ->options([
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                7 => 'Minggu',
                            ])
                            ->required(),

                        Select::make('work_shift_id')
                            ->relationship('workShift', 'name')
                            ->required()
                            ->createOptionForm([
                                Section::make('Shift Information')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->label('Shift Name'),
                                        TextInput::make('break_minutes')
                                            ->suffix('Menit')
                                            ->numeric(),
                                        TextInput::make('late_tolerance')
                                            ->suffix('Menit')
                                            ->numeric(),
                                        TextInput::make('overtime_tolerance')
                                            ->suffix('Menit')
                                            ->numeric(),
                                    ]),

                                Section::make('Shift Times')
                                    ->columns(2)
                                    ->schema([
                                        TimePicker::make('start_time')
                                            ->required(),
                                        TimePicker::make('end_time')
                                            ->required(),
                                    ])
                            ]),

                        Radio::make('is_working_days')
                            ->label('Apakah Jadwal ini termasuk hari kerja?')
                            ->boolean()
                            ->inline()
                            ->required()
                            ->default(true),


                ])
            ]);
    }
}
