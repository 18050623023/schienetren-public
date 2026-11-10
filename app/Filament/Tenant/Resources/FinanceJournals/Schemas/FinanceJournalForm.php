<?php

namespace App\Filament\Tenant\Resources\FinanceJournals\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

class FinanceJournalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Jurnal')
                    ->columns(3)
                    ->schema([
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->default(fn () => session('current_branch_id'))
                            ->required()
                            ->searchable(),
                            
                        DatePicker::make('date')
                            ->label('Tanggal Jurnal')
                            ->required()
                            ->default(now()),
                            
                        TextInput::make('reference_number')
                            ->label('No. Referensi (Otomatis jika kosong)')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                            
                        Textarea::make('description')
                            ->label('Keterangan / Memo')
                            ->required()
                            ->columnSpanFull(),
                            
                        Hidden::make('created_by')
                            ->default(fn () => \Illuminate\Support\Facades\Auth::id()),
                    ]),
                    
                Section::make('Detail Transaksi (Double-Entry)')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->columns(4)
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                    if (is_array($value)) {
                                        foreach ($value as $item) {
                                            $totalDebit += (float) ($item['debit'] ?? 0);
                                            $totalCredit += (float) ($item['credit'] ?? 0);
                                        }
                                        if (abs($totalDebit - $totalCredit) > 0.001) {
                                            $fail("Total Debit ({$totalDebit}) harus sama dengan Total Kredit ({$totalCredit}).");
                                        }
                                        if ($totalDebit == 0) {
                                            $fail("Nilai transaksi tidak boleh nol.");
                                        }
                                    }
                                };
                            })
                            ->schema([
                                Select::make('finance_account_id')
                                    ->label('Akun')
                                    ->relationship('account', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                    
                                TextInput::make('debit')
                                    ->label('Debit')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                    
                                TextInput::make('credit')
                                    ->label('Kredit')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                    
                                TextInput::make('description')
                                    ->label('Keterangan per baris (Opsional)')
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(2)
                            ->minItems(2)
                            ->addActionLabel('Tambah Baris Jurnal')
                    ])
            ]);
    }
}
