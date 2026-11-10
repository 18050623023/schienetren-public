<?php

namespace App\Filament\Admin\Resources\PaymentConfirmations;

use App\Filament\Admin\Resources\PaymentConfirmations\Pages\CreatePaymentConfirmation;
use App\Filament\Admin\Resources\PaymentConfirmations\Pages\EditPaymentConfirmation;
use App\Filament\Admin\Resources\PaymentConfirmations\Pages\ListPaymentConfirmations;
use App\Filament\Admin\Resources\PaymentConfirmations\Schemas\PaymentConfirmationForm;
use App\Filament\Admin\Resources\PaymentConfirmations\Tables\PaymentConfirmationsTable;
use App\Models\PaymentConfirmation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentConfirmationResource extends Resource
{
    protected static ?string $model = PaymentConfirmation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // protected static ?string $recordTitleAttribute = 'invoice.invoice_number';

    public static function form(Schema $schema): Schema
    {
        return PaymentConfirmationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentConfirmationsTable::configure($table);
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
            'index' => ListPaymentConfirmations::route('/'),
            // 'create' => CreatePaymentConfirmation::route('/create'),
            // 'edit' => EditPaymentConfirmation::route('/{record}/edit'),
        ];
    }

    // Menentukan kolom apa saja yang bisa dicari dari fitur Global Search (Search bar atas)
    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice.invoice_number', 'sender_bank_name', 'sender_account_name'];
    }

    // Mengatur judul hasil pencariannya agar menampilkan Nomor Invoice
    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return 'Pembayaran: ' . ($record->invoice->invoice_number ?? 'Tanpa Invoice');
    }

    // Mengubah judul record di Header (Modal View, Edit, Delete, Breadcrumb)
    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record?->invoice?->invoice_number ?? 'Pembayaran';
    }

}
