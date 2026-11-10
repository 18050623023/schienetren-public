<?php

namespace App\Filament\Portal\Pages;

use Filament\Pages\Page;
use App\Models\Plan;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentConfirmation;
use App\Models\Subscription;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use App\Models\Promo;
use App\Models\Wallet;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Blade;

class Checkout extends Page implements HasForms
{
    use InteractsWithForms;

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'filament.portal.pages.checkout';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Checkout';

    public ?array $checkoutIntent = [];
    public ?array $data = [];
    public ?string $appliedPromoCode = null;

    #[On('promo-selected')]
    public function applyPromoCode($promo_code)
    {
        $this->appliedPromoCode = $promo_code;
        Notification::make()
            ->title('Promo Diterapkan')
            ->success()
            ->send();
        $this->unmountAction();
    }

    public function removePromoCode()
    {
        $this->appliedPromoCode = null;
        Notification::make()
            ->title('Promo Dihapus')
            ->warning()
            ->send();
    }

    public function mount(): void
    {
        $intent = session('checkout_intent');
        // dd($intent);
        if (!$intent) {
            redirect(Billing::getUrl());
            return;
        }

        // Protection: Cek apakah ada invoice unpaid untuk item yang sama
        $itemType = null;
        $itemId = null;

        if (($intent['type'] ?? '') === 'subscription') {
            $itemType = 'plan';
            $itemId = $intent['plan_id'] ?? null;
        } elseif (($intent['type'] ?? '') === 'manual_topup') {
            $itemType = 'topup';
        } elseif (($intent['type'] ?? '') === 'addon') {
            $itemType = 'addon';
            $itemId = $intent['addon_id'] ?? null;
        }

        if ($itemType) {
            $pendingInvoicesQuery = Invoice::where('user_id', Auth::id())
                ->whereIn('status', ['unpaid', 'waiting_approval']);

            $existingInvoice = null;

            if ($itemType === 'plan') {
                // Cari tagihan plan APAPUN yang masih gantung
                $existingPlanInvoice = (clone $pendingInvoicesQuery)
                    ->whereHas('items', function ($query) use ($itemType) {
                        $query->where('item_type', $itemType);
                    })->first();

                if ($existingPlanInvoice) {
                    // Karena form checkout mengharuskan upload bukti transfer, 
                    // kita tidak boleh membatalkan tagihan lama sembarangan.
                    // Jika ada tagihan Plan (apapun plan-nya) yang belum selesai, langsung blokir.
                    $existingInvoice = $existingPlanInvoice;
                }
            } else {
                // Untuk addon / topup, gunakan logika asal (cek spesifik item_id)
                $existingInvoice = (clone $pendingInvoicesQuery)
                    ->whereHas('items', function ($query) use ($itemType, $itemId) {
                        $query->where('item_type', $itemType);
                        if ($itemId) {
                            $query->where('item_id', $itemId);
                        }
                    })
                    ->first();
            }

            if ($existingInvoice) {
                session()->forget('checkout_intent');
                Notification::make()
                    ->title('Tagihan Tertunda')
                    ->body('Anda masih memiliki tagihan yang belum di Approve untuk item ini. Silakan follow up Admin via Whatsapp atau batalkan tagihan sebelumnya melalui halaman Billing.')
                    ->warning()
                    ->persistent()
                    ->send();

                redirect(Billing::getUrl());
                return;
            }
        }

        $this->checkoutIntent = $intent;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $intent = $this->checkoutIntent;
        $plan = isset($intent['plan_id']) ? Plan::find($intent['plan_id']) : null;
        $addon = isset($intent['addon_id']) ? \App\Models\Addon::find($intent['addon_id']) : null;
        $user = Auth::user();
        $activeSub = $user->getActiveSubscription();
        $userTenants = $user->tenants;

        return $schema
            ->components([
                Section::make('Makin Hemat Pakai Promo!')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('active_promo')
                            ->hiddenLabel()
                            ->content(function () use ($intent, $plan, $addon) {
                                if (!$this->appliedPromoCode) {
                                    return new HtmlString("<div class='text-gray-500 text-sm'>Belum ada promo yang terpasang.</div>");
                                }
                                $promo = Promo::where('code', $this->appliedPromoCode)->first();
                                if (!$promo) return null;

                                $price = 0;
                                if (($intent['type'] ?? '') === 'subscription') {
                                    $price = $plan ? $plan->price : 0;
                                } elseif (($intent['type'] ?? '') === 'manual_topup') {
                                    $price = $intent['amount'] ?? 0;
                                } elseif (($intent['type'] ?? '') === 'addon') {
                                    $price = $addon ? $addon->price : 0;
                                }

                                $discountAmount = $promo->calculateDiscount($price);
                                $discountFormatted = 'Rp ' . number_format($discountAmount, 0, ',', '.');

                                $html = "<div class='flex items-center justify-between p-3 bg-green-50 rounded-lg'>";
                                $html .= "<div class='flex items-center gap-3'>";
                                $html .= "<div class='text-green-600'><svg class='w-6 h-6' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' /></svg></div>";
                                $html .= "<div>";
                                $html .= "<div class='font-bold text-green-700 uppercase'>{$promo->code}</div>";
                                $html .= "<div class='text-sm text-green-600'>Yeay! Kamu hemat {$discountFormatted}</div>";
                                $html .= "</div></div></div>";
                                return new HtmlString($html);
                            }),

                        Actions::make([
                            Action::make('selectPromo')
                                ->label(fn() => $this->appliedPromoCode ? 'Ganti Promo' : 'Lihat Promo')
                                ->icon('heroicon-o-tag')
                                ->color('success')
                                ->modalHeading('Pilih Promo')
                                ->modalSubmitAction(false)
                                ->modalCancelAction(false)
                                ->modalContent(function () use ($intent, $plan, $addon) {
                                    $price = 0;
                                    $itemType = 'all';

                                    if (($intent['type'] ?? '') === 'subscription') {
                                        $price = $plan ? $plan->price : 0;
                                        $itemType = 'plan';
                                    } elseif (($intent['type'] ?? '') === 'manual_topup') {
                                        $price = $intent['amount'] ?? 0;
                                        $itemType = 'topup';
                                    } elseif (($intent['type'] ?? '') === 'addon') {
                                        $price = $addon ? $addon->price : 0;
                                        $itemType = 'addon';
                                    }

                                    return new HtmlString(Blade::render('@livewire("promo-table-modal", ["subtotal" => $subtotal, "itemType" => $itemType])', [
                                        'subtotal' => $price,
                                        'itemType' => $itemType,
                                    ]));
                                }),

                            Action::make('removePromo')
                                ->label('Hapus')
                                ->icon('heroicon-o-x-mark')
                                ->color('danger')
                                ->action('removePromoCode')
                                ->visible(fn() => $this->appliedPromoCode !== null)
                        ])->alignEnd(),
                    ]),


                Section::make('Pilih Bisnis Tujuan')
                    ->hidden(function () use ($intent) {
                        return ($intent['type'] ?? '') !== 'addon';
                    })
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Pilih Bisnis')
                            ->options(fn() => $userTenants->pluck('business_name', 'id'))
                            ->required()
                            ->default(fn() => $userTenants->first()?->id)
                            ->reactive(),
                    ]),

                Section::make('Pembayaran')
                    ->schema([
                        \Filament\Forms\Components\Radio::make('payment_method')
                            ->label('Pilih Metode Pembayaran')
                            ->options(function () use ($intent, $user) {
                                $options = [
                                    'bank_transfer' => 'Transfer Bank Manual'
                                ];
                                if (($intent['type'] ?? '') !== 'manual_topup') {
                                    $balance = $user->wallet->balance ?? 0;
                                    $options['wallet'] = 'Saldo Wallet (Rp ' . number_format($balance, 0, ',', '.') . ')';
                                }
                                return $options;
                            })
                            ->descriptions(function () use ($intent, $user, $plan, $addon) {
                                if (($intent['type'] ?? '') === 'manual_topup') return [];
                                
                                $total = $this->getTotalPrice($intent, $plan, $addon);
                                $balance = $user->wallet->balance ?? 0;
                                
                                if ($balance < $total) {
                                    return ['wallet' => 'Saldo tidak cukup'];
                                }
                                
                                return [];
                            })
                            ->disableOptionWhen(function (string $value) use ($intent, $user, $plan, $addon) {
                                if ($value !== 'wallet' || ($intent['type'] ?? '') === 'manual_topup') {
                                    return false;
                                }
                                
                                $total = $this->getTotalPrice($intent, $plan, $addon);
                                $balance = $user->wallet->balance ?? 0;
                                
                                return $balance < $total;
                            })
                            ->default('bank_transfer')
                            ->required()
                            ->reactive(),

                        Group::make([
                            Grid::make(3)
                                ->schema([
                                    Select::make('bank_account_id')
                                        ->columnSpan(1)
                                        ->label('Bank Tujuan')
                                        ->options(BankAccount::where('is_active', true)->pluck('bank_name', 'id'))
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, callable $set) => $set('bank_account_id', $state)),
    
                                    Placeholder::make('payment_instructions')
                                        ->columnSpan(2)
                                        ->label('Instruksi Pembayaran')
                                        ->hidden(fn(Get $get) => !$get('bank_account_id'))
                                        ->content(function (Get $get) {
                                            $method = BankAccount::find($get('bank_account_id'));
                                            if (!$method) return null;
    
                                            $html = "<div class='text-sm text-gray-600'>";
                                            if ($method->account_number) {
                                                $html .= "<p class='font-semibold'>Nomor Rekening: " . $method->account_number . "</p>";
                                            }
                                            if ($method->account_holder) {
                                                $html .= "<p>Atas Nama: " . $method->account_holder . "</p>";
                                            }
                                            if ($method->instructions) {
                                                // $html .= "<p class='mt-2'>" . nl2br(e()) $method->instructions. "</p>";
                                                $html .= "<p class='mt-2'>" . $method->instructions . "</p>";
                                            }
                                            $html .= "</div>";
    
                                            return new HtmlString($html);
                                        }),
    
                                ]),
                            Grid::make(2)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('sender_bank_name')
                                        ->label('Nama Bank Pengirim')
                                        ->placeholder('BCA, Mandiri, dll.')
                                        ->required(),
    
                                    \Filament\Forms\Components\TextInput::make('sender_account_name')
                                        ->label('Nama Pemilik Rekening Pengirim')
                                        ->placeholder('Sesuai dengan nama di rekening')
                                        ->required(),
                                ]),
    
                            FileUpload::make('receipt_path')
                                ->label('Unggah Bukti Pembayaran')
                                ->image()
                                ->maxSize(5120)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                ->visibility('private')
                                ->directory(fn() => 'file/payment-subscription/receipts/' . Auth::id())
                                ->getUploadedFileNameForStorageUsing(
                                    // fn(UploadedFile $file): string => 'receipt-' . now()->format('Y-m-d_His') . '.' . $file->getClientOriginalExtension(),
                                    fn(UploadedFile $file): string => 'receipt-' . now()->format('Y-m-d_His') . '.' . ($file->guessExtension() ?? 'bin'),
                                )
                                ->required(),
                        ])->visible(fn(Get $get) => $get('payment_method') === 'bank_transfer'),
                    ]),

                Section::make('Ringkasan Pesanan')
                    ->schema([
                        Callout::make('subscription_alert')
                            ->hidden(function () use ($intent, $activeSub) {
                                if (($intent['type'] ?? '') === 'addon') return false; // Show for addon
                                if (($intent['type'] ?? '') !== 'subscription') return true; // Hide for others
                                return !$activeSub; // Show for subscription if there's an active sub
                            })
                            ->heading(function () use ($plan, $activeSub, $intent) {
                                if (($intent['type'] ?? '') === 'addon') return 'Penambahan Addon';
                                if (!$plan || !$activeSub || !$activeSub->plan) return '';

                                $oldPlan = $activeSub->plan;

                                if ($plan->id == $oldPlan->id) return 'Perpanjang Paket';
                                return 'PENTING: Pergantian Paket';
                            })
                            ->description(function (Get $get) use ($plan, $userTenants, $activeSub, $intent, $addon) {
                                if (($intent['type'] ?? '') === 'addon') {
                                    $tenantId = $get('tenant_id');
                                    if ($tenantId && $addon) {
                                        $tenant = $userTenants->where('id', $tenantId)->first();
                                        if ($tenant) {
                                            $addonFeature = $addon->addonFeatures()->first();
                                            $addonKey = $addonFeature ? ($addonFeature->feature->code ?? null) : null;
                                            $currentValidUntil = $tenant->data['addons'][$addonKey] ?? null;

                                            if ($currentValidUntil && \Carbon\Carbon::parse($currentValidUntil)->isFuture()) {
                                                $formattedDate = \Carbon\Carbon::parse($currentValidUntil)->translatedFormat('d F Y');
                                                return "Addon ini sedang aktif pada bisnis yang Anda pilih hingga {$formattedDate}. Pembelian ini akan memperpanjang masa aktif Addon Anda.";
                                            }
                                        }
                                    }
                                    return 'Addon akan langsung diaktifkan setelah pembayaran berhasil.';
                                }

                                if (!$plan || !$activeSub || !$activeSub->plan) return '';

                                $oldPlan = $activeSub->plan;

                                if ($plan->id == $oldPlan->id) {
                                    return 'Paket Anda saat ini akan diperpanjang. Sisa hari akan diakumulasikan.';
                                }

                                if ($plan->price >= $oldPlan->price) {
                                    return new HtmlString('Paket baru akan langsung aktif dan menggantikan paket Anda saat ini. <br> <strong>Sisa hari pada paket lama akan hangus.</strong> <br> <em>Catatan: Jika Anda memiliki antrean perpanjangan paket (status Scheduled), paket tersebut akan dibatalkan dan dananya akan otomatis dikembalikan ke Saldo Wallet Anda.</em>');
                                } else {
                                    return new HtmlString("Paket baru akan berstatus 'dijadwalkan' dan baru akan mulai aktif secara otomatis setelah masa berlaku paket Anda saat ini habis.");
                                }
                            })
                            ->status(function (Get $get) use ($plan, $userTenants, $activeSub, $intent, $addon) {
                                if (($intent['type'] ?? '') === 'addon') {
                                    $tenantId = $get('tenant_id');
                                    if ($tenantId && $addon) {
                                        $tenant = $userTenants->where('id', $tenantId)->first();
                                        if ($tenant) {
                                            $addonFeature = $addon->addonFeatures()->first();
                                            $addonKey = $addonFeature ? ($addonFeature->feature->code ?? null) : null;
                                            $currentValidUntil = $tenant->data['addons'][$addonKey] ?? null;

                                            if ($currentValidUntil && \Carbon\Carbon::parse($currentValidUntil)->isFuture()) {
                                                return 'info';
                                            }
                                        }
                                    }
                                    return 'success';
                                }

                                if (!$plan || !$activeSub || !$activeSub->plan) return 'info';

                                $oldPlan = $activeSub->plan;

                                if ($plan->id == $oldPlan->id) return 'info';
                                if ($plan->price >= $oldPlan->price) return 'danger';
                                return 'info';
                            })
                            ->columnSpanFull(),
                        Placeholder::make('plan_name')
                            ->label('Item Tagihan')
                            ->content(function () use ($intent, $plan, $addon) {
                                if (($intent['type'] ?? '') === 'subscription') {
                                    return $plan ? 'Langganan: ' . $plan->name : '-';
                                } elseif (($intent['type'] ?? '') === 'addon') {
                                    return $addon ? 'Addon: ' . $addon->name : '-';
                                } elseif (($intent['type'] ?? '') === 'manual_topup') {
                                    return 'Top Up Manual';
                                }
                                return '-';
                            }),
                        Placeholder::make('plan_price')
                            ->label('Rincian Tagihan')
                            ->content(function (Get $get) use ($intent, $plan, $addon) {
                                $price = 0;
                                $receivedBalance = null;

                                if (($intent['type'] ?? '') === 'subscription') {
                                    $price = $plan ? $plan->price : 0;
                                } elseif (($intent['type'] ?? '') === 'manual_topup') {
                                    $price = $intent['amount'] ?? 0;
                                } elseif (($intent['type'] ?? '') === 'addon') {
                                    $price = $addon ? $addon->price : 0;
                                }

                                // $adminFee = env('GLOBAL_ADMIN_FEE', 0);
                                $adminFee = settingCentral('global_admin_fee', 0);

                                $discountAmount = 0;
                                if ($this->appliedPromoCode) {
                                    $promo = Promo::where('code', $this->appliedPromoCode)->first();
                                    if ($promo) {
                                        $discountAmount = $promo->calculateDiscount($price);
                                    }
                                }

                                $total = $this->getTotalPrice($intent, $plan, $addon);

                                $html = "<div class='text-sm space-y-1' style='min-width: 200px;'>";

                                $html .= "<div style='display: flex; justify-content: space-between; font-weight: 500;'><span>Subtotal:</span> <span>Rp " . number_format($price, 0, ',', '.') . "</span></div>";

                                if ($discountAmount > 0) {
                                    $html .= "<div style='display: flex; justify-content: space-between; color: #10b981; font-weight: 500;'><span>Diskon ({$this->appliedPromoCode}):</span> <span>-Rp " . number_format($discountAmount, 0, ',', '.') . "</span></div>";
                                }

                                if ($adminFee > 0) {
                                    $html .= "<div style='display: flex; justify-content: space-between; color: #6b7280;'><span>Biaya Admin:</span> <span>Rp " . number_format($adminFee, 0, ',', '.') . "</span></div>";
                                }
                                $html .= "<div style='display: flex; justify-content: space-between; font-weight: bold; padding-top: 0.5rem; margin-top: 0.5rem; border-top: 1px solid #e5e7eb;'><span>Total:</span> <span>Rp " . number_format($total, 0, ',', '.') . "</span></div>";
                                $html .= "</div>";

                                return new HtmlString($html);
                            }),
                    ])->columns(2),

            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Proses Pembayaran')
                ->submit('submit')
                ->color('primary')
        ];
    }

    public function getTotalPrice($intent, $plan, $addon)
    {
        $price = 0;
        if (($intent['type'] ?? '') === 'subscription') {
            $price = $plan ? $plan->price : 0;
        } elseif (($intent['type'] ?? '') === 'manual_topup') {
            $price = $intent['amount'] ?? 0;
        } elseif (($intent['type'] ?? '') === 'addon') {
            $price = $addon ? $addon->price : 0;
        }

        $adminFee = settingCentral('global_admin_fee', 0);

        $discountAmount = 0;
        if ($this->appliedPromoCode) {
            $promo = Promo::where('code', $this->appliedPromoCode)->first();
            if ($promo) {
                $discountAmount = $promo->calculateDiscount($price);
            }
        }

        return $price - $discountAmount + $adminFee;
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $intent = $this->checkoutIntent;
        $user = Auth::user();

        try {
            $action = app(\App\Filament\Portal\Pages\Checkout\SubmitCheckout::class);
            $action->execute($data, $intent, $user, $this->appliedPromoCode);

            session()->forget('checkout_intent');
            Notification::make()
                ->title('Pesanan Berhasil')
            ->body('Bukti pembayaran Anda telah kami terima dan akan segera diproses.')
                ->success()
                ->send();

            redirect(Billing::getUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
