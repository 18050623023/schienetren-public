<?php

namespace App\Filament\Portal\Pages;

use Filament\Pages\Page;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Addon;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Storage;

class Billing extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static string|UnitEnum|null $navigationGroup = 'Account';
    protected static ?string $title = 'Billing & Subscription';

    protected string $view = 'filament.portal.pages.billing';

    public function getActiveSubscriptionProperty()
    {
        return Auth::user()->getActiveSubscription();
    }

    public function getScheduledSubscriptionProperty()
    {
        return Auth::user()->getScheduledSubscription();
    }

    public function toggleAutoRenew()
    {
        $sub = $this->activeSubscription;
        if ($sub) {
            $sub->update(['auto_renew' => !$sub->auto_renew]);
            Notification::make()
                ->title($sub->auto_renew ? 'Auto-Renew Diaktifkan' : 'Auto-Renew Dimatikan')
                ->success()
                ->send();
        }
    }




    protected function getHeaderActions(): array
    {
        return [
            Action::make('subscribe')
                ->label(fn() => $this->activeSubscription ? 'Upgrade Paket' : 'Mulai Berlangganan')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalContent(fn() => view('filament.portal.components.pricing-cards', [
                    'plans' => \App\Services\BillingService::formatPlansForCards(Plan::with('planFeatures.feature')->where('is_active', true)->get(), false),
                    'currentPlanId' => $this->activeSubscription ? $this->activeSubscription->plan_id : null,
                ])),

            Action::make('buyAddon')
                ->label('Beli Addon')
                ->icon('heroicon-o-puzzle-piece')
                ->color('success')
                ->modalWidth('7xl')
                ->modalHeading('Pilih Addon Tambahan')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->visible(fn() => $this->activeSubscription)
                ->modalContent(fn() => view('filament.portal.components.pricing-cards', [
                    'plans' => \App\Services\BillingService::formatPlansForCards(Addon::with('addonFeatures.feature')->where('is_active', true)->get(), true),
                    'currentPlanId' => null, // No specific current plan for addons view
                ])),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'balance' => Auth::user()->wallet->balance ?? 0,
        ];
    }

    public function subscribeToPlan($planId)
    {
        session()->put('checkout_intent', ['type' => 'subscription', 'plan_id' => $planId]);
        return redirect(Checkout::getUrl());
    }

    public function buyAddonAction($addonId)
    {
        session()->put('checkout_intent', ['type' => 'addon', 'addon_id' => $addonId]);
        return redirect(Checkout::getUrl());
    }

    public function manualTopupAction(): Action
    {
        return Action::make('manualTopup')
            ->label('Top Up Sekarang')
            ->modalHeading('Top Up')
            ->form([
                TextInput::make('amount')
                    ->label('Nominal Top Up')
                    ->numeric()
                    ->required()
                    ->minValue(10000),
            ])
            ->action(function (array $data) {
                session()->put('checkout_intent', [
                    'type' => 'manual_topup',
                    'amount' => $data['amount']
                ]);
                return redirect(Checkout::getUrl());
            });
    }

    public function viewPromosAction(): Action
    {
        return Action::make('viewPromos')
            ->label('Lihat Promo')
            ->icon('heroicon-o-gift')
            ->color('success')
            ->modalHeading('Paket Top Up Promo')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(fn() => view('filament.portal.components.promo-packages', [
                // 'packages' => \App\Models\TopupPackage::where('is_active', true)->get(),
            ]));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('user_id', Auth::id())
                    ->latest()
            )
            ->columns([
                TextColumn::make('invoice_number')->label('No Invoice')->searchable(),
                TextColumn::make('description')
                    ->label('Tujuan Pembayaran')
                    ->getStateUsing(function (Invoice $record) {
                        $item = $record->items->first();
                        if (!$item) return '-';
                        if ($item->item_type === 'plan') {
                            return 'Langganan: ' . ($item->item->name ?? 'Plan');
                        } elseif ($item->item_type === 'addon') {
                            return 'Addon: ' . ($item->item->name ?? 'Addon');
                        } elseif ($item->item_type === 'topup') {
                            return 'Topup Saldo';
                        }
                        return ucwords(str_replace('_', ' ', $item->item_type));
                    }),
                TextColumn::make('total_amount')->label('Jumlah')->money('IDR'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'waiting_approval' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Tanggal')->dateTime(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn($record) => in_array($record->status, ['unpaid', 'waiting_approval']))
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled']);

                            // If there is a payment confirmation waiting, cancel it too
                            if ($record->paymentConfirmation && $record->paymentConfirmation->status === 'pending') {
                                $record->paymentConfirmation->update([
                                    'status' => 'rejected',
                                    'rejection_reason' => 'Dibatalkan oleh klien.'
                                ]);
                            }

                            Notification::make()
                                ->title('Tagihan dibatalkan')
                                ->success()
                                ->send();
                        }),
                    ViewAction::make()
                        ->label('Detail')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('invoice_number')
                                    ->label('Invoice Number'),
                                TextEntry::make('payment_channel')
                                    ->label('Metode Pembayaran'),
                                TextEntry::make('total_amount')
                                    ->label('Total')
                                    ->money('IDR'),
                                TextEntry::make('items_description')
                                    ->label('Pembayaran Untuk')
                                    ->formatStateUsing(function ($record) {
                                        $descriptions = [];
                                        foreach ($record->items as $item) {
                                            if ($item->item_type === 'plan') {
                                                $descriptions[] = 'Paket Langganan: ' . ($item->item ? $item->item->name : 'Unknown');
                                            } elseif ($item->item_type === 'topup') {
                                                $descriptions[] = 'Top Up Saldo';
                                            } elseif ($item->item_type === 'addon') {
                                                $descriptions[] = 'Addon: ' . ($item->item ? $item->item->name : 'Unknown');
                                            } else {
                                                $descriptions[] = ucfirst($item->item_type);
                                            }
                                        }
                                        return implode(', ', $descriptions) ?: '-';
                                    }),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'unpaid' => 'danger',
                                        'waiting_approval' => 'warning',
                                        'paid' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'gray',
                                        'expired' => 'gray',
                                        default => 'gray',
                                    }),
                                TextEntry::make('created_at')
                                    ->label('Dibuat pada')
                                    ->dateTime(),
                                TextEntry::make('paid_at')
                                    ->label('Dibayar pada')
                                    ->dateTime(),
                            ]),
                            Fieldset::make('Informasi Pembayaran Manual')
                                ->relationship('paymentConfirmation')
                                ->visible(fn($record) => $record->paymentConfirmation !== null)
                                ->schema([
                                    TextEntry::make('sender_bank_name')->label('Bank Pengirim'),
                                    TextEntry::make('sender_account_name')->label('Nama Pengirim'),
                                    TextEntry::make('transfer_amount')->label('Nominal Transfer')->money('IDR'),
                                    TextEntry::make('status')
                                        ->label('Status Validasi')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        }),

                                    ImageEntry::make('proof_file_path')
                                        ->label('Preview Bukti Pembayaran')
                                        ->width(500)
                                        ->openUrlInNewTab()
                                        ->columnSpanFull() 
                                        ->url(fn($record) => Storage::temporaryUrl($record->proof_file_path, now()->addMinutes(5)))
                                        ->visible(function ($record) {
                                            if (!$record || !$record->proof_file_path) return false;
                                            $ext = strtolower(pathinfo($record->proof_file_path, PATHINFO_EXTENSION));
                                            return in_array($ext, config('custom.file.image_extensions', []));
                                        }), 

                                    TextEntry::make('proof_file_path_link')
                                        ->hiddenLabel()
                                        ->state(function ($record) {
                                            if (!$record || !$record->proof_file_path) return '';

                                            $ext = strtolower(pathinfo($record->proof_file_path, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, config('custom.file.image_extensions', []));
                                            
                                            // return $isImage ? 'Buka Gambar Penuh (Tab Baru)' : 'Download / Lihat Dokumen Bukti';
                                            return 'Download / Lihat Dokumen Bukti';
                                        })
                                        ->url(fn($record): ?string => $record->proof_file_path ? Storage::download($record->proof_file_path) : null)
                                        ->openUrlInNewTab()
                                        ->color('primary')
                                        ->icon(function ($record) {
                                            if (!$record || !$record->proof_file_path) return 'heroicon-o-document';
                                            $ext = strtolower(pathinfo($record->proof_file_path, PATHINFO_EXTENSION));
                                            return in_array($ext, config('custom.file.image_extensions', []))
                                                ? 'heroicon-o-arrow-top-right-on-square'
                                                : 'heroicon-o-arrow-down-tray';
                                        })
                                        ->columnSpanFull()
                                        ->visible(fn($record) => $record->proof_file_path !== null),
                                ])->columns(4)
                        ])
                ])
            ])
            ->emptyStateHeading('Belum ada riwayat tagihan');
    }
}
