<?php

namespace App\Filament\Tenant\Resources\CustomerReceivables\Tables;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Repairs\RepairResource;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\RepairInvoice;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerReceivablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('repair.code')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label('Pelanggan')
                    ->state(fn (RepairInvoice $record): string => 
                        $record->repair?->client?->name ?? $record->repair?->customer_name ?? 'Pelanggan Umum'
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('repair', function ($q) use ($search) {
                            $q->where('customer_name', 'like', "%{$search}%")
                              ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                        });
                    }),

                TextColumn::make('client_phone')
                    ->label('Kontak / WA')
                    ->state(fn (RepairInvoice $record): string => 
                        $record->repair?->client?->phone ?? $record->repair?->customer_phone ?? '-'
                    )
                    ->copyable(),

                TextColumn::make('repair.branch.name')
                    ->label('Cabang')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => 
                        $state && Carbon::parse($state)->endOfDay()->isPast() ? 'danger' : 'gray'
                    ),

                TextColumn::make('grand_total')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Terbayar')
                    ->money('IDR', locale: 'id')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('remaining_amount')
                    ->label('Sisa Piutang')
                    ->money('IDR', locale: 'id')
                    ->badge()
                    ->color('danger')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'partially_paid' => 'info',
                        'unpaid' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'partially_paid' => 'Cicilan (Parsial)',
                        'unpaid' => 'Belum Dibayar',
                        default => $state,
                    }),
            ])
            ->defaultSort('due_date', 'asc')
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('repair.branch', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    // 1. Aksi Bayar / Pelunasan Piutang Idempotent
                    Action::make('pay_receivable')
                        ->label('Bayar / Cicil')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->modalHeading('Pembayaran Piutang Servis')
                        ->modalDescription(fn (RepairInvoice $record) => 
                            "Invoice: {$record->invoice_number} | Tiket: " . ($record->repair?->code ?? '-')
                        )
                        ->modalWidth('md')
                        ->fillForm(function (RepairInvoice $record): array {
                            $prefix = rtrim(settingTenant('pay_repair_prefix', 'PAY'), '-');
                            return [
                                'remaining_info' => 'Rp ' . number_format($record->remaining_amount, 0, ',', '.'),
                                'payment_amount' => $record->remaining_amount,
                                'payment_date' => now()->toDateString(),
                                'payment_method_id' => PaymentMethod::first()?->id ?? 1,
                                'reference_number' => "{$prefix}-INV-{$record->id}-" . date('YmdHis') . '-' . Str::random(4),
                                'notes' => '',
                            ];
                        })
                        ->form([
                            TextInput::make('remaining_info')
                                ->label('Sisa Piutang Saat Ini')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('payment_amount')
                                ->label('Nominal Pembayaran')
                                ->prefix('Rp')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->helperText('Masukkan nominal yang dibayar oleh pelanggan.'),

                            DatePicker::make('payment_date')
                                ->label('Tanggal Pembayaran')
                                ->default(now())
                                ->required(),

                            Select::make('payment_method_id')
                                ->label('Metode Pembayaran')
                                ->options(fn () => PaymentMethod::pluck('name', 'id'))
                                ->required(),

                            Textarea::make('notes')
                                ->label('Catatan')
                                ->placeholder('Misal: Pelunasan saat pengambilan unit')
                                ->rows(2),

                            Hidden::make('reference_number'),
                        ])
                        ->action(function (array $data, RepairInvoice $record) {
                            $payAmount = (float) ($data['payment_amount'] ?? 0);

                            if ($payAmount <= 0) {
                                Notification::make()->title('Nominal pembayaran harus lebih dari 0')->danger()->send();
                                throw new Halt();
                            }

                            DB::transaction(function () use ($record, $data, $payAmount) {
                                // A. Pessimistic Lock pada baris invoice
                                $invoice = RepairInvoice::where('id', $record->id)->lockForUpdate()->first();
                                if (! $invoice) {
                                    Notification::make()->title('Data invoice tidak ditemukan')->danger()->send();
                                    throw new Halt();
                                }

                                // B. Overpayment Guard
                                if ($payAmount > $invoice->remaining_amount) {
                                    Notification::make()
                                        ->title('Pembayaran Melebihi Sisa Piutang')
                                        ->body('Sisa piutang saat ini: Rp ' . number_format($invoice->remaining_amount, 0, ',', '.'))
                                        ->danger()
                                        ->send();
                                    throw new Halt();
                                }

                                // C. Idempotency Key
                                $prefix = rtrim(settingTenant('pay_repair_prefix', 'PAY'), '-');
                                $refNumber = $data['reference_number'] ?? ("{$prefix}-INV-{$invoice->id}-" . date('YmdHis') . '-' . Str::random(4));

                                // D. Deduplication Guard: firstOrCreate pada repairPayments
                                $payment = $invoice->repairPayments()->firstOrCreate(
                                    [
                                        'reference_number' => $refNumber,
                                    ],
                                    [
                                        'payment_method_id' => $data['payment_method_id'],
                                        'amount' => $payAmount,
                                        'payment_date' => $data['payment_date'],
                                        'notes' => $data['notes'] ?? null,
                                        'created_by' => Auth::id(),
                                    ]
                                );

                                // E. Sinkronkan total dan sisa tagihan via observer / recalculateTotals
                                $invoice->recalculateTotals();
                            });

                            Notification::make()
                                ->title('Pembayaran Piutang Berhasil Dicatat')
                                ->body('Pembayaran sebesar Rp ' . number_format($payAmount, 0, ',', '.') . ' telah dibukukan.')
                                ->success()
                                ->send();
                        }),

                    // 2. Aksi Kirim Pengingat Tagihan via WhatsApp
                    Action::make('whatsapp_reminder')
                        ->label('Kirim WA')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(function (RepairInvoice $record): ?string {
                            $phone = $record->repair?->client?->phone ?? $record->repair?->customer_phone ?? '';
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            if (empty($cleanPhone)) return null;

                            $clientName = $record->repair?->client?->name ?? $record->repair?->customer_name ?? 'Pelanggan';
                            $amount = number_format($record->remaining_amount, 0, ',', '.');
                            $dueDate = $record->due_date ? Carbon::parse($record->due_date)->translatedFormat('d F Y') : '-';
                            $brandName = settingTenant('brand_name', 'Schienetren');
                            
                            $defaultTemplate = "Halo Kak {customer_name},\n\nKami menginformasikan bahwa tagihan servis Anda di {brand_name} (Tiket: {ticket_code}) memiliki sisa piutang sebesar *Rp {amount}* dengan tanggal jatuh tempo *{due_date}*.\n\nMohon konfirmasi atau pelunasan saat pengambilan unit. Terima kasih!";
                            $template = settingTenant('repair_whatsapp_reminder_template', $defaultTemplate) ?: $defaultTemplate;

                            $msg = str_replace(
                                ['{customer_name}', '{brand_name}', '{ticket_code}', '{amount}', '{due_date}'],
                                [$clientName, $brandName, $record->repair?->code ?? '-', $amount, $dueDate],
                                $template
                            );
                            
                            return "https://wa.me/{$cleanPhone}?text=" . rawurlencode($msg);
                        })
                        ->openUrlInNewTab()
                        ->visible(fn (RepairInvoice $record): bool => 
                            !empty($record->repair?->client?->phone ?? $record->repair?->customer_phone)
                        ),

                    // 3. Aksi Lihat Detail Reparasi
                    Action::make('view_repair')
                        ->label('Lihat Tiket')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn (RepairInvoice $record): ?string => 
                            $record->repair?->code ? (RepairResource::getUrl('index') . '?tableSearch=' . urlencode($record->repair->code)) : null
                        )
                        ->visible(fn (RepairInvoice $record): bool => (bool)$record->repair_id),
                ]),
            ]);
    }
}
