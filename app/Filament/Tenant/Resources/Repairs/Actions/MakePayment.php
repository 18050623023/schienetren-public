<?php

namespace App\Filament\Tenant\Resources\Repairs\Actions;

use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Repair;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakePayment
{
    /**
     * Create the configured action. Pass the action instance (Table Action or Page Action) to this method.
     *
     * @param mixed $action The action to configure (e.g., \Filament\Tables\Actions\Action or \Filament\Actions\Action)
     * @return mixed
     */
    public static function configure($action)
    {
        return $action
            //         ->label('Pembayaran')
            // ->icon('heroicon-o-banknotes')
            // ->color('success')
            // ->modalHeading('Pembayaran Servis')
            ->label(function (?Repair $record = null): string {
                if (! $record) return 'Pembayaran';
                $invoice = $record->repairInvoice;
                if ($invoice && (float)$invoice->remaining_amount > 0) {
                    return 'Pembayaran (Sisa: Rp ' . number_format($invoice->remaining_amount, 0, ',', '.') . ')';
                }
                if ($invoice && $invoice->grand_total > 0 && (float)$invoice->remaining_amount <= 0) {
                    return 'Lihat Pembayaran (Lunas)';
                }
                return 'Pembayaran';
            })
            ->badge(function (?Repair $record = null): ?string {
                if (! $record) return null;
                $invoice = $record->repairInvoice;
                if ($invoice && (float)$invoice->remaining_amount > 0) {
                    return 'Sisa Rp ' . number_format($invoice->remaining_amount, 0, ',', '.');
                }
                if ($invoice && $invoice->grand_total > 0 && (float)$invoice->remaining_amount <= 0) {
                    return 'Lunas';
                }
                return null;
            })
            ->badgeColor(function (?Repair $record = null): string {
                $invoice = $record?->repairInvoice;
                return ($invoice && (float)$invoice->remaining_amount > 0) ? 'danger' : 'success';
            })
            ->icon(function (?Repair $record = null): string {
                $invoice = $record?->repairInvoice;
                return ($invoice && (float)$invoice->remaining_amount > 0) ? 'heroicon-o-credit-card' : 'heroicon-o-banknotes';
            })
            ->color(function (?Repair $record = null): string {
                $invoice = $record?->repairInvoice;
                return ($invoice && (float)$invoice->remaining_amount > 0) ? 'warning' : 'success';
            })
            ->modalHeading(function (?Repair $record = null): string {
                if (! $record) return 'Pembayaran Servis';
                $invoice = $record->repairInvoice;
                if ($invoice && (float)$invoice->remaining_amount > 0) {
                    return 'Pembayaran / Pelunasan Piutang Servis';
                }
                return 'Rincian Pembayaran Servis';
            })
            ->modalWidth('4xl')
            ->visible(function (Repair $record) {
                //                 if (in_array($record->status->value ?? $record->status, ['cancel', 'delivered'])) {
                //     return false;
                // }
                $status = $record->status->value ?? $record->status;

                // Jika tiket dibatalkan (cancel) dan tidak pernah ada uang masuk, sembunyikan
                if ($status === 'cancel') {
                    $invoice = $record->repairInvoice;
                    return $invoice && (float) $invoice->paid_amount > 0;
                }

                // Untuk status lainnya (termasuk 'delivered'), tetap tampilkan
                // agar kasir bisa melunasi sisa piutang tempo atau melihat riwayat pembayaran
                return true;
            })
            ->modalSubmitAction(fn($action, ?Repair $record = null) => $action->visible(function () use ($record) {
                if (! $record) return true;

                $invoice = $record->repairInvoice;
                if (!$invoice) return true;
                return $invoice->remaining_amount > 0 || $invoice->grand_total == 0;
            }))
            ->fillForm(function (Repair $record): array {
                $remaining = $record->repairInvoice ? $record->repairInvoice->remaining_amount : 0;

                $existingPayments = [];
                if ($record->repairInvoice) {
                    $existingPayments = $record->repairInvoice->repairPayments->map(function ($p) {
                        $isRefund = $p->type === 'refund';
                        return [
                            'payment_date' => $p->payment_date,
                            'type' => $isRefund ? '🔴 OUT (Refund)' : '🟢 IN (Masuk)',
                            'payment_method' => $p->paymentMethod->name ?? '-',
                            'reference_number' => $p->reference_number,
                            'amount' => $p->amount,
                        ];
                    })->toArray();
                }

                return [
                    'existing_payments' => $existingPayments,
                    'payments' => [
                        [
                            'payment_method_id' => null,
                            'amount' => $remaining > 0 ? $remaining : null,
                            'reference_number' => rtrim(settingTenant('pay_repair_prefix', 'PAY'), '-') . '-' . strtoupper(Str::random(6)),
                            'payment_date' => now()->format('Y-m-d H:i:s'),
                            'notes' => null,
                        ]
                    ]
                ];
            })
            ->schema(function (Repair $record) {
                $invoice = $record->repairInvoice;
                // dd($invoice);
                $grandTotal = $invoice ? $invoice->grand_total : 0;
                $paidAmount = $invoice ? $invoice->paid_amount : 0;
                $remainingAmount = $invoice ? $invoice->remaining_amount : 0;

                return [
                    Grid::make(4)
                        ->schema([
                            TextEntry::make('grand_total')
                                ->label('Total Tagihan')
                                ->state('Rp ' . number_format($grandTotal, 0, ',', '.')),
                            TextEntry::make('paid_amount')
                                ->label('Sudah Dibayar (DP)')
                                ->state('Rp ' . number_format($paidAmount, 0, ',', '.')),
                            TextEntry::make('current_remaining')
                                ->label('Sisa Tagihan')
                                ->state('Rp ' . number_format($remainingAmount, 0, ',', '.')),
                            TextEntry::make('remaining_amount')
                                ->label('Sisa Setelah Input')
                                ->state(function (Get $get) use ($remainingAmount) {
                                    $totalInput = collect($get('payments'))->sum('amount');
                                    $currentRemaining = $remainingAmount - $totalInput;
                                    $color = $currentRemaining < 0 ? 'text-danger-600' : 'text-primary-600';

                                    $prefix = $currentRemaining < 0 ? 'Lebih/Kembalian: ' : '';
                                    $displayAmount = abs($currentRemaining);

                                    return new \Illuminate\Support\HtmlString(
                                        "<div class='font-bold {$color}'>{$prefix}Rp " . number_format($displayAmount, 0, ',', '.') . "</div>" .
                                            "<div class='text-xs text-graypad-500 font-normal mt-1 leading-tight'>*Dihitung otomatis berdasarkan nominal pembayaran yang diketik di bawah.</div>"
                                    );
                                }),
                        ]),

                    Repeater::make('existing_payments')
                        ->label('Riwayat Pembayaran & Pengembalian Dana (Refund)')
                        ->columns(5)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->table([
                            TableColumn::make('Tanggal'),
                            TableColumn::make('Tipe (In / Out)'),
                            TableColumn::make('Metode'),
                            TableColumn::make('No. Referensi'),
                            TableColumn::make('Nominal'),
                        ])
                        ->compact()
                        ->schema([
                            TextInput::make('payment_date')
                                ->readOnly(),
                            TextInput::make('type')
                                ->readOnly(),
                            TextInput::make('payment_method')
                                ->readOnly(),
                            TextInput::make('reference_number')
                                ->readOnly(),
                            TextInput::make('amount')
                                ->prefix('Rp')
                                ->numeric()
                                ->readOnly(),
                        ])
                        ->visible(fn(Get $get) => count($get('existing_payments') ?? []) > 0)
                        ->columnSpanFull(),

                    Repeater::make('payments')
                        ->label('Metode Pembayaran')
                        ->visible(function (Repair $record) {
                            $invoice = $record->repairInvoice;
                            if (!$invoice) return true;
                            return $invoice->remaining_amount > 0 || $invoice->grand_total == 0;
                        })
                        ->schema([
                            Select::make('payment_method_id')
                                ->label('Metode')
                                ->options(PaymentMethod::where('is_active', true)->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->columnSpan(2),
                            TextInput::make('amount')
                                ->label('Nominal')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->live(debounce: 500)
                                ->columnSpan(2),
                            TextInput::make('reference_number')
                                ->label('No. Referensi')
                                ->required()
                                ->columnSpan(2)
                                ->default(fn() => 'PAY-' . strtoupper(Str::random(6))),
                            DateTimePicker::make('payment_date')
                                ->label('Waktu Pembayaran')
                                ->required()
                                ->columnSpan(2)
                                ->default(now()),
                            Textarea::make('notes')
                                ->label('Catatan')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->addActionLabel('Tambah Split Pembayaran')
                        ->live()
                ];
            })
            ->action(function (array $data, Repair $record) {
                // $invoice = $record->repairInvoice;

                $payments = $data['payments'] ?? [];
                $totalInput = collect($payments)->sum('amount');

                if ($totalInput <= 0) {
                    Notification::make()->title('Nominal pembayaran tidak valid!')->danger()->send();
                    throw new Halt();
                }

                DB::transaction(function () use ($payments, $record, $totalInput) {
                    $invoice = \App\Models\Tenant\RepairInvoice::findOrCreateForRepair($record);
                    // Pessimistic lock pada invoice untuk konsistensi perhitungan saldo
                    $invoice = \App\Models\Tenant\RepairInvoice::where('id', $invoice->id)->lockForUpdate()->first();

                    // Hitung nominal pembayaran baru (abaikan yang sudah pernah tercatat jika ini adalah retry request)
                    $existingRefSum = $invoice->repairPayments()
                        ->whereIn('reference_number', collect($payments)->pluck('reference_number'))
                        ->sum('amount');
                    $newAmount = max(0, $totalInput - $existingRefSum);

                    if ($invoice->grand_total > 0 && $newAmount > $invoice->remaining_amount) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Total pembayaran (Rp ' . number_format($totalInput, 0, ',', '.') . ') melebihi sisa tagihan.')
                            ->danger()
                            ->send();

                        throw new Halt();
                    }

                    // Create repair payments secara idempotent
                    foreach ($payments as $payment) {
                        $invoice->repairPayments()->firstOrCreate(
                            ['reference_number' => $payment['reference_number']],
                            [
                                'payment_method_id' => $payment['payment_method_id'],
                                'amount' => $payment['amount'],
                                'payment_date' => $payment['payment_date'],
                                'notes' => $payment['notes'] ?? null,
                                'created_by' => \Illuminate\Support\Facades\Auth::id(),
                            ]
                        );
                    }

                    // Panggil fungsi recalculate totals agar otomatis update subtotal, grand_total, paid_amount, remaining_amount, dan status
                    $invoice->recalculateTotals();
                });

                Notification::make()
                    ->title('Pembayaran Berhasil')
                    ->body('Pembayaran sebesar Rp ' . number_format($totalInput, 0, ',', '.') . ' telah dicatat.')
                    ->success()
                    ->send();
            });
    }
}
