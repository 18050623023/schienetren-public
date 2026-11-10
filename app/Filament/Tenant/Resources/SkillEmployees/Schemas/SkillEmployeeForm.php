<?php

namespace App\Filament\Tenant\Resources\SkillEmployees\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class SkillEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                    ->required(),
                Select::make('skill_specialization_id')
                    ->label('Skill')
                    ->relationship('skillSpecialization', 'name')
                    ->required(),

                Select::make('skill_level_id')
                    ->label('Level')
                    ->relationship('skillLevel', 'name')
                    ->required(),
                // Repeater::make('skills')
                //     ->label('Skill')
                //     ->columnSpan(3)
                //     ->schema([

                //     ])
                //     ->columns(2)
            ]);
    }
}
