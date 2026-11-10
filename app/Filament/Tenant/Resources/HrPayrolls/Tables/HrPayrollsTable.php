<?php

namespace App\Filament\Tenant\Resources\HrPayrolls\Tables;

use App\Models\Tenant\HrPayroll;
use App\Models\Tenant\PaymentMethod;
use App\Services\PayrollCalculationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HrPayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('period', 'desc')
            ->columns([
                TextColumn::make('period')
                    ->label('Periode')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('employee.user.name')
                    ->label('Karyawan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('basic_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_allowance')
                    ->label('Tunjangan & Komisi')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_deduction')
                    ->label('Potongan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Lunas / Paid',
                        'draft' => 'Draft',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'paid' => 'Lunas / Paid',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('pay')
                        ->label('Bayar Gaji')
                        ->icon(Heroicon::OutlinedBanknotes)
                        ->color('success')
                        ->modalHeading('Konfirmasi Pembayaran Gaji')
                        ->visible(fn (HrPayroll $record) => $record->status === 'draft')
                        ->schema([
                            Select::make('payment_method_id')
                                ->label('Metode Pembayaran Kas/Bank')
                                ->options(PaymentMethod::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (HrPayroll $record, array $data) {
                            DB::transaction(function () use ($record, $data) {
                                $locked = HrPayroll::where('id', $record->id)->lockForUpdate()->first();
                                if (!$locked || $locked->status !== 'draft') {
                                    return;
                                }

                                $locked->update([
                                    'status' => 'paid',
                                    'payment_method_id' => $data['payment_method_id'],
                                ]);
                            });

                            Notification::make()
                                ->title('Gaji Berhasil Dibayarkan')
                                ->body('Status slip gaji diperbarui menjadi Paid dan pengeluaran kas telah dicatat.')
                                ->success()
                                ->send();
                        }),

                    Action::make('recalculate')
                        ->label('Hitung Ulang')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('gray')
                        ->visible(fn (HrPayroll $record) => $record->status === 'draft')
                        ->requiresConfirmation()
                        ->action(function (HrPayroll $record) {
                            app(PayrollCalculationService::class)->calculatePayroll(
                                $record->employee_id,
                                $record->period,
                                $record->branch_id
                            );

                            Notification::make()
                                ->title('Kalkulasi Gaji Diperbarui')
                                ->body('Data komponen dan komisi servis telah dihitung ulang dari data terbaru.')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->visible(fn (HrPayroll $record) => $record->status === 'draft'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkPay')
                        ->label('Bayar Slip Gaji Terpilih')
                        ->icon(Heroicon::OutlinedBanknotes)
                        ->color('success')
                        ->requiresConfirmation()
                        ->schema([
                            Select::make('payment_method_id')
                                ->label('Metode Pembayaran Kas/Bank')
                                ->options(PaymentMethod::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = DB::transaction(function () use ($records, $data) {
                                $c = 0;
                                foreach ($records as $record) {
                                    $locked = HrPayroll::where('id', $record->id)->lockForUpdate()->first();
                                    if ($locked && $locked->status === 'draft') {
                                        $locked->update([
                                            'status' => 'paid',
                                            'payment_method_id' => $data['payment_method_id'],
                                        ]);
                                        $c++;
                                    }
                                }
                                return $c;
                            });

                            Notification::make()
                                ->title('Gaji Berhasil Dibayarkan')
                                ->body("Sebanyak {$count} slip gaji berhasil dibayarkan dan dicatat ke Finance.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
