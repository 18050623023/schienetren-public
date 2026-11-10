<?php

namespace App\Filament\Tenant\Resources\PaymentMethods;

use App\Filament\Tenant\Resources\PaymentMethods\Pages\CreatePaymentMethod;
use App\Filament\Tenant\Resources\PaymentMethods\Pages\EditPaymentMethod;
use App\Filament\Tenant\Resources\PaymentMethods\Pages\ListPaymentMethods;
use App\Filament\Tenant\Resources\PaymentMethods\Schemas\PaymentMethodForm;
use App\Filament\Tenant\Resources\PaymentMethods\Tables\PaymentMethodsTable;
use App\Models\Tenant\PaymentMethod;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Payment Method';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return PaymentMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentMethodsTable::configure($table);
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
            'index' => ListPaymentMethods::route('/'),
            'create' => CreatePaymentMethod::route('/create'),
            'edit' => EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
