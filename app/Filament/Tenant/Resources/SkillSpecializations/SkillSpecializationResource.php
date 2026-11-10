<?php

namespace App\Filament\Tenant\Resources\SkillSpecializations;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\SkillSpecializations\Pages\CreateSkillSpecialization;
use App\Filament\Tenant\Resources\SkillSpecializations\Pages\EditSkillSpecialization;
use App\Filament\Tenant\Resources\SkillSpecializations\Pages\ListSkillSpecializations;
use App\Filament\Tenant\Resources\SkillSpecializations\Schemas\SkillSpecializationForm;
use App\Filament\Tenant\Resources\SkillSpecializations\Tables\SkillSpecializationsTable;
use App\Models\Tenant\SkillSpecialization;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SkillSpecializationResource extends Resource
{
    protected static ?string $model = SkillSpecialization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Skill Specialization';

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
            return false;
        }

        return \Illuminate\Support\Facades\Auth::user()?->can('viewAny:skill_specialization') ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return SkillSpecializationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillSpecializationsTable::configure($table);
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
            'index' => ListSkillSpecializations::route('/'),
            'create' => CreateSkillSpecialization::route('/create'),
            'edit' => EditSkillSpecialization::route('/{record}/edit'),
        ];
    }
}
