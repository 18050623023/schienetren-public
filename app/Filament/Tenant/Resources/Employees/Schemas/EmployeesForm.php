<?php

namespace App\Filament\Tenant\Resources\Employees\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Date;

class EmployeesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code_pegawai')
                    ->required(),

                Radio::make('is_active')
                    ->boolean()
                    ->label('Apakah karyawan ini aktif?')
                    ->reactive()
                    ->inline()
                    ->default(true),

                Section::make('Informasi Karyawan')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->suffixAction(
                                Action::make('add_role')
                                    ->icon('heroicon-o-plus')
                                    ->url(route('filament.tenant.resources.users.create'))
                                    ->openUrlInNewTab()
                                    ->tooltip('Add Role')
                            ),
                        DatePicker::make('join_date')
                            ->default(Date::now()),
                        TextInput::make('phone')
                            ->required()
                            ->tel()
                            ->placeholder('8xxxxxxxxxx')
                            ->telRegex('/^8[1-9][0-9]{6,10}$/')
                            ->prefix('+62')
                            ->suffixAction(
                                Action::make('copy')
                                    ->icon('heroicon-o-clipboard')
                                    ->action(fn (Set $set, Get $get) => $set('whatsapp', $get('phone')))
                                    ->tooltip('Copy to WhatsApp')
                            ),
                        TextInput::make('whatsapp')
                            ->required()
                            ->prefix('+62')
                            ->tel(),
                        Select::make('gender')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                        ]),
                        Textarea::make('address')
                            ->columnSpan(2)
                            ->required(),
                    ]),

                // Section::make('Informasi Karyawan')
                //     ->columnSpanFull()
                //     ->columns(4)
                //     ->schema([
                //     ])
                //     ->collapsible(),

                // Section::make('Skills')
                //     ->columns(2)
                //     ->schema([

                //     ])
                //     ->collapsible(),

                Repeater::make('skills')
                    // ->columnSpan(2)
                    ->relationship('skills') // relasi ke pivot / hasMany
                    ->schema([
                        Select::make('skill_specialization_id')
                            ->label('Skill')
                            ->relationship('skillSpecialization', 'name')
                            ->required()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Skill Name')
                                    ->required(),
                            ]),

                        Select::make('skill_level_id')
                            ->label('Level')
                            ->relationship('skillLevel', 'name')
                            ->required()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Level Name')
                                    ->required(),
                            ]),
                    ])
                    ->columns(2),

                Repeater::make('employeeAssignments')
                    ->relationship()
                    ->columnSpan(2)
                    ->columns(5)
                    ->visibleOn('view')
                    ->schema([
                        Select::make('branch_id')
                            ->relationship('branch', 'name'),

                        Select::make('work_schedule_id')
                            ->relationship('workSchedule', 'name'),

                        DatePicker::make('start_date'),

                        DatePicker::make('end_date'),

                        Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name'),
                    ]),
                // Repeater::make('roles')
                //     ->relationship('roles') // relasi ke pivot / hasMany
                //     ->schema([
                //         Select::make('role_id')
                //             ->relationship('role', 'name')
                //             ->required()
                //     ])
            ]);
    }
}
