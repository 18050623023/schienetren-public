<?php

namespace App\Filament\Tenant\Resources\SkillLevels;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\SkillLevels\Pages\CreateSkillLevel;
use App\Filament\Tenant\Resources\SkillLevels\Pages\EditSkillLevel;
use App\Filament\Tenant\Resources\SkillLevels\Pages\ListSkillLevels;
use App\Filament\Tenant\Resources\SkillLevels\Schemas\SkillLevelForm;
use App\Filament\Tenant\Resources\SkillLevels\Tables\SkillLevelsTable;
use App\Models\Tenant\SkillLevel;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SkillLevelResource extends Resource
{
    protected static ?string $model = SkillLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Skill Level';
    // protected static string|UnitEnum|null $navigationGroup = 'Skills';
    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:skill_level') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return SkillLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkillLevels::route('/'),
            'create' => CreateSkillLevel::route('/create'),
            'edit' => EditSkillLevel::route('/{record}/edit'),
        ];
    }
}
