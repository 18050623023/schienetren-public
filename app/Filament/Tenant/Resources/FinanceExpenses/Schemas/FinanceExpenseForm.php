<?php

namespace App\Filament\Tenant\Resources\FinanceExpenses\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;

class FinanceExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->default(fn() => session('current_branch_id'))
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('finance_account_id')
                    ->label('Akun Pengeluaran')
                    ->relationship(
                        'account',
                        'name',
                        fn(Builder $query) =>
                        $query->whereHas('category', fn($q) => $q->where('type', 'expense'))
                    )
                    ->required(fn () => tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE))
                    ->visible(fn () => tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE))
                    ->searchable()
                    ->preload(),

                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),

                Select::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('amount')
                    ->label('Nominal')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->maxValue(999999999999.99),

                TextInput::make('reference_number')
                    ->label('Nomor Referensi (Opsional)')
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Deskripsi/Keterangan')
                    ->required()
                    ->columnSpanFull(),

                Hidden::make('created_by')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
            ]);
    }
}
