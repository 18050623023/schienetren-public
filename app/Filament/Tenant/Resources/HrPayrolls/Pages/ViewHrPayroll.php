<?php

namespace App\Filament\Tenant\Resources\HrPayrolls\Pages;

use App\Filament\Tenant\Resources\HrPayrolls\HrPayrollResource;
use App\Models\Tenant\HrPayroll;
use App\Models\Tenant\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class ViewHrPayroll extends ViewRecord
{
    protected static string $resource = HrPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                        ->body('Status slip gaji diperbarui menjadi Paid dan pengeluaran kas telah dicatat ke Finance.')
                        ->success()
                        ->send();
                }),

            EditAction::make()
                ->visible(fn (HrPayroll $record) => $record->status === 'draft'),
        ];
    }
}
