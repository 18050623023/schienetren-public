<?php

namespace App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims;

use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Pages\CreateRepairWarrantyClaim;
use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Pages\EditRepairWarrantyClaim;
use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Pages\ListRepairWarrantyClaims;
use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Schemas\RepairWarrantyClaimForm;
use App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Tables\RepairWarrantyClaimsTable;
use App\Models\Tenant\RepairWarrantyClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RepairWarrantyClaimResource extends Resource
{
    protected static ?string $model = RepairWarrantyClaim::class;

    public static function getNavigationGroup(): ?string
    {
        // return "";
        return \Illuminate\Support\Facades\Auth::user()?->hasRole(array_merge(
            (array) settingTenant('tenant_technical_roles', ['technician']),
            (array) settingTenant('tenant_bypass_roles', ['owner'])
        ))
            ? 'Work In Progress'
            : '';
    }
    protected static ?string $modelLabel = 'Klaim Garansi';
    protected static ?string $pluralModelLabel = 'Klaim Garansi';
    protected static ?string $slug = 'repair-warranty-claims';
    // protected static ?string $recordTitleAttribute = 'Klaim Garansi';


    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    public static function form(Schema $schema): Schema
    {
        return RepairWarrantyClaimForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairWarrantyClaimsTable::configure($table);
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
            'index' => ListRepairWarrantyClaims::route('/'),
            'create' => CreateRepairWarrantyClaim::route('/create'),
            'edit' => EditRepairWarrantyClaim::route('/{record}/edit'),
        ];
    }
}
