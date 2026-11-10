<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\Actions;

use App\Enums\TenantFeature;
use App\Models\Tenant\FinanceExpense;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\PurchaseOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaySupplierDebtAction
{
    /**
     * Configure action for table row or page header
     *
     * @param mixed $action
     * @return mixed
     */
    public static function configure($action)
    {
        return $action
            ->label('Bayar Hutang')
            ->icon('heroicon-o-credit-card')
            ->color('success')
            ->modalHeading('Pembayaran Hutang Supplier')
            ->modalDescription(fn(PurchaseOrder $record) => "PO: {$record->code} — Supplier: " . ($record->supplier->name ?? 'Supplier'))
            ->modalWidth('md')
            ->visible(fn(PurchaseOrder $record) => tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER) && in_array($record->status, ['confirmed', 'partially', 'completed']) && $record->remaining_amount > 0)
            ->fillForm(fn(PurchaseOrder $record): array => [
                'remaining_info' => 'Rp ' . number_format($record->remaining_amount, 0, ',', '.'),
                'payment_amount' => $record->remaining_amount,
                'payment_date' => now()->toDateString(),
                'payment_method_id' => PaymentMethod::first()?->id ?? 1,
                'reference_number' => 'PAY-' . $record->code . '-' . date('YmdHis') . '-' . Str::random(4),
                'notes' => '',
            ])
            ->schema([
                TextInput::make('remaining_info')
                    ->label('Sisa Hutang Saat Ini')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('payment_amount')
                    ->label('Nominal Pembayaran')
                    ->prefix('Rp')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->helperText('Masukkan nominal yang akan dibayarkan ke supplier.'),

                DatePicker::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->default(now())
                    ->required(),

                Select::make('payment_method_id')
                    ->label('Metode Pembayaran (Sumber Kas)')
                    ->options(fn() => PaymentMethod::pluck('name', 'id'))
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan / Bukti Transfer')
                    ->placeholder('Misal: Transfer pelunasan via m-Banking BCA')
                    ->rows(2),

                Hidden::make('reference_number'),
            ])
            ->action(function (array $data, PurchaseOrder $record) {
                abort_unless(tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER), 403, 'Fitur Purchase Order tidak aktif.');

                $payAmount = (float) ($data['payment_amount'] ?? 0);

                if ($payAmount <= 0) {
                    Notification::make()->title('Nominal pembayaran harus lebih dari 0')->danger()->send();
                    throw new Halt();
                }

                DB::transaction(function () use ($record, $data, $payAmount) {
                    // 1. Pessimistic Lock pada baris PO untuk mencegah race condition
                    $po = PurchaseOrder::where('id', $record->id)->lockForUpdate()->first();
                    if (! $po) {
                        Notification::make()->title('Data PO tidak ditemukan')->danger()->send();
                        throw new Halt();
                    }

                    // 2. Overpayment Guard: Cek di bawah lock
                    if ($payAmount > $po->remaining_amount) {
                        Notification::make()
                            ->title('Pembayaran Melebihi Sisa Hutang')
                            ->body('Sisa hutang saat ini: Rp ' . number_format($po->remaining_amount, 0, ',', '.'))
                            ->danger()
                            ->send();
                        throw new Halt();
                    }

                    // 3. Idempotency Key unik deterministik
                    $prefix = rtrim(settingTenant('pay_debt_prefix', 'PAY'), '-');
                    $refNumber = $data['reference_number'] ?? ($prefix . '-' . $po->code . '-' . date('YmdHis') . '-' . Str::random(4));

                    // 4. Deduplication Guard: firstOrCreate mencegah dobel insert jika terjadi retry request / double click
                    $expense = FinanceExpense::firstOrCreate(
                        [
                            'reference_number' => $refNumber,
                        ],
                        [
                            'branch_id' => $po->branch_id,
                            'finance_account_id' => settingTenant('finance_account_ap', 5),
                            'date' => $data['payment_date'],
                            'amount' => $payAmount,
                            'payment_method_id' => $data['payment_method_id'],
                            'description' => 'Pembayaran Hutang PO ' . $po->code . ' (' . ($po->supplier->name ?? 'Supplier') . ')' . (!empty($data['notes']) ? ' - ' . $data['notes'] : ''),
                            'created_by' => Auth::id(),
                        ]
                    );

                    // 5. Update PO balance jika baru dibuat (wasRecentlyCreated)
                    if ($expense->wasRecentlyCreated) {
                        $newPaid = $po->paid_amount + $payAmount;
                        $netTotal = max(0, $po->total - $po->discount);
                        $newRemaining = max(0, $netTotal - $newPaid);

                        $po->update([
                            'paid_amount' => $newPaid,
                            'remaining_amount' => $newRemaining,
                        ]);
                    }
                });

                Notification::make()
                    ->title('Pembayaran Hutang Berhasil Dicatat')
                    ->body('Kas keluar sebesar Rp ' . number_format($payAmount, 0, ',', '.') . ' telah dibukukan dan sisa hutang PO telah diperbarui.')
                    ->success()
                    ->send();
            });
    }
}
