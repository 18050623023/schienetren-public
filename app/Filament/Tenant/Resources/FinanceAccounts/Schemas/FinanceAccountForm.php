<?php

namespace App\Filament\Tenant\Resources\FinanceAccounts\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use App\Models\Tenant\FinanceAccountCategory;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class FinanceAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Nama Akun')
                    ->required()
                    ->maxLength(255),
                Select::make('finance_account_category_id')
                    ->label('Kategori Akun')
                    ->relationship('category', 'name')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state, ?string $old) {
                        if ($state) {
                            $category = FinanceAccountCategory::find($state);
                            if ($category && $category->code_prefix) {
                                $currentCode = $get('code');
                                if (empty($currentCode)) {
                                    $set('code', $category->code_prefix);
                                } elseif ($old) {
                                    $oldCategory = FinanceAccountCategory::find($old);
                                    if ($oldCategory && $oldCategory->code_prefix && str_starts_with($currentCode, $oldCategory->code_prefix)) {
                                        $newCode = $category->code_prefix . substr($currentCode, strlen($oldCategory->code_prefix));
                                        $set('code', $newCode);
                                    }
                                }
                            }
                        }
                    })
                    ->createOptionForm([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Kategori')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('type')
                                    ->label('Tipe')
                                    ->options([
                                        'asset' => 'Asset (Aset)',
                                        'liability' => 'Liability (Kewajiban)',
                                        'equity' => 'Equity (Ekuitas)',
                                        'revenue' => 'Revenue (Pendapatan)',
                                        'expense' => 'Expense (Pengeluaran)',
                                    ])
                                    ->required(),
                                TextInput::make('code_prefix')
                                    ->label('Awalan Kode')
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->columnSpanFull()
                                    ->label('Deskripsi')
                                    ->maxLength(65535),
                            ])
                    ]),
                TextInput::make('code')
                    ->label('Kode Akun')
                    ->required()
                    ->live()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        if (empty($get('finance_account_category_id'))) {
                            Notification::make()
                                ->danger()
                                ->title('Kategori Akun tidak boleh kosong')
                                ->send();
                            $set('code', null);
                        }
                    }),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
