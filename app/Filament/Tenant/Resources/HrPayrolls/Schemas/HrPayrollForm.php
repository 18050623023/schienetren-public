<?php

namespace App\Filament\Tenant\Resources\HrPayrolls\Schemas;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Employee;
use App\Models\Tenant\PaymentMethod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HrPayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penggajian')
                    ->columns(2)
                    ->schema([
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->required()
                            ->disabledOn('edit'),

                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name)
                            ->required()
                            ->disabledOn('edit'),

                        TextInput::make('period')
                            ->label('Periode (YYYY-MM)')
                            ->placeholder('2026-10')
                            ->required()
                            ->disabledOn('edit'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'paid' => 'Dibayarkan (Paid)',
                            ])
                            ->default('draft')
                            ->required(),

                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Ringkasan Nominal')
                    ->columns(2)
                    ->schema([
                        TextInput::make('basic_salary')
                            ->label('Gaji Pokok')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('total_allowance')
                            ->label('Total Tunjangan & Komisi')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('total_deduction')
                            ->label('Total Potongan')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('net_salary')
                            ->label('Gaji Bersih (Take Home Pay)')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-bold text-primary-600']),
                    ]),

                Section::make('Rincian Komponen Gaji')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->columns(3)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipe')
                                    // ->columnSpanFull()
                                    ->options([
                                        'earning' => 'Penghasilan / Tunjangan',
                                        'commission' => 'Komisi (Servis / Item)',
                                        'deduction' => 'Potongan',
                                    ])
                                    ->required(),

                                TextInput::make('name')
                                    ->label('Nama Komponen')
                                    ->required(),

                                TextInput::make('amount')
                                    ->label('Nominal')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ]);
    }
}
