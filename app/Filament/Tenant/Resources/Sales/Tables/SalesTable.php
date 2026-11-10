<?php

namespace App\Filament\Tenant\Resources\Sales\Tables;

use App\Models\Tenant\Branch;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Sale;
use App\Services\Pos\PosCheckoutService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $currentBranchId = session('current_branch_id');
                if ($currentBranchId) {
                    $query->where('branch_id', $currentBranchId);
                }
            })
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('date')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->visible(fn () => empty(session('current_branch_id'))),

                TextColumn::make('client.name')
                    ->label('Pelanggan')
                    ->default('Walk-in Customer (Umum)')
                    ->searchable(),

                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->label('Total Transaksi')
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('paymentMethod.name')
                    ->label('Metode Bayar')
                    ->badge()
                    ->default('Tunai'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'refunded' => 'warning',
                        'void' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'refunded' => 'Refund',
                        'void' => 'Dibatalkan (Void)',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->options(Branch::all()->pluck('name', 'id'))
                    ->visible(fn () => empty(session('current_branch_id'))),

                SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->options(PaymentMethod::where('is_active', true)->pluck('name', 'id')),

                SelectFilter::make('status')
                    ->label('Status Transaksi')
                    ->options([
                        'completed' => 'Selesai',
                        'void' => 'Dibatalkan (Void)',
                        'refunded' => 'Refund',
                    ]),
            ])
            ->actions([
                Action::make('print')
                    ->label('Cetak Struk')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->color('amber')
                    ->url(fn (Sale $record) => route('tenant.pos.sales.print', $record), shouldOpenInNewTab: true),

                Action::make('void')
                    ->label('Batalkan (Void)')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(function (Sale $record) {
                        $user = Auth::user();
                        if (!$user || $record->status !== 'completed') return false;
                        $bypassRoles = settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']);
                        return $user->hasRole($bypassRoles) || $user->hasRole(['manager']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembatalan Transaksi POS')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan transaksi ini? Stok barang akan dikembalikan ke cabang dan mutasi stok pembalik akan otomatis dibuat.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Pembatalan (Wajib)')
                            ->required()
                            ->placeholder('Contoh: Kesalahan input kasir / barang dikembalikan pelanggan.'),
                    ])
                    ->action(function (Sale $record, array $data, PosCheckoutService $service) {
                        try {
                            $service->voidSale($record, $data['reason']);
                            Notification::make()
                                ->title("Transaksi #{$record->invoice_number} berhasil dibatalkan dan stok dikembalikan.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal membatalkan transaksi: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
