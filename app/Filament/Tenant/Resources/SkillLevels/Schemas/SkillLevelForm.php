<?php

namespace App\Filament\Tenant\Resources\SkillLevels\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class SkillLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
