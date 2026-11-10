<?php

namespace App\Filament\Tenant\Resources\Repairs\RepairWarrantyClaims\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;

class RepairWarrantyClaimForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Klaim Garansi')
                    ->schema([
                        Placeholder::make('item_name')
                            ->label('Item / Servis yang Diklaim')
                            ->content(function ($record) {
                                if (!$record) return '-';
                                $productName = $record->repairItemWarranty->repairItem->productVariant->product->name ?? '';
                                $variantName = $record->repairItemWarranty->repairItem->productVariant->name ?? '';
                                return trim($productName . ' ' . $variantName);
                            }),
                        Placeholder::make('repair_code')
                            ->label('Kode Perbaikan Asal')
                            ->content(fn ($record) => $record?->repairItemWarranty?->repairItem?->repairDevice?->repair?->code ?? '-'),
                        Placeholder::make('warranty_period')
                            ->label('Masa Garansi')
                            ->content(function ($record) {
                                if (!$record) return '-';
                                $start = $record->repairItemWarranty->start_date;
                                $end = $record->repairItemWarranty->end_date;
                                return $start . ' s/d ' . $end;
                            }),
                    ])->columns(3),
                    
                Section::make('Informasi Masalah')
                    ->schema([
                        DatePicker::make('claim_date')
                            ->label('Tanggal Klaim')
                            ->required()
                            ->disabled(),
                        Textarea::make('problems')
                            ->label('Keluhan / Masalah')
                            ->required()
                            ->columnSpanFull()
                            ->disabled(),
                    ]),

                Section::make('Penanganan')
                ->columnSpanFull()
                    // ->headerActions([
                        
                    // ])
                    ->schema([
                        Select::make('status')
                            ->label('Status Klaim')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'Sedang Diproses',
                                'completed' => 'Selesai / Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled(fn () => Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician'])) )
                            ->dehydrated()
                            ->required(),
                        Select::make('handled_by')
                            ->label('Ditangani Oleh')
                            ->relationship('handledBy', 'name')
                            ->default(Auth::id())
                            ->disabled(fn () => Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician'])) )
                            ->dehydrated()
                            ->required(),

                        Actions::make([
                            Action::make('create_rma')
                                ->label('Buat Repair')
                                ->icon('heroicon-o-document-plus')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->modalHeading('Buat Repair Baru')
                                ->modalDescription('Apakah Anda yakin ingin membuat tiket Repair baru untuk garansi ini? Data pelanggan dan perangkat akan disalin.')
                                ->schema(fn () => Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician'])) ? [
                                    \Filament\Forms\Components\Radio::make('assignment')
                                        ->label('Penugasan Teknisi')
                                        ->inline()
                                        ->options([
                                            'todo' => 'Antrean Umum (Todo)',
                                            'checking' => 'Saya Langsung Kerjakan (Checking)',
                                        ])
                                        ->default('todo')
                                        ->required(),
                                ] : [])
                                ->visible(fn ($record) => $record !== null && empty($record->new_repair_id))
                                ->action(function ($record, array $data, Get $get) {
                                    if (!$record) return;

                                    $originalRepairDevice = $record->repairItemWarranty?->repairItem?->repairDevice;
                                    if (!$originalRepairDevice || !$originalRepairDevice->repair) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Gagal')
                                            ->body('Data perangkat asli tidak ditemukan.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $originalRepair = $originalRepairDevice->repair;
                                    $branchId = $originalRepair->branch_id ?? session('current_branch_id');
                                    $branch = \App\Models\Tenant\Branch::find($branchId);

                                    if (!$branch) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Gagal')
                                            ->body('Data cabang tidak ditemukan.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $isTechnician = Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician']));
                                    $handledByUserId = null;
                                    $activeAssignment = null;

                                    if ($isTechnician) {
                                        $assignment = $data['assignment'] ?? 'todo';
                                        if ($assignment === 'checking') {
                                            $handledByUserId = Auth::id();
                                        }
                                    } else {
                                        $handledByUserId = $get('handled_by');
                                    }

                                    if ($handledByUserId) {
                                        $handledUser = \App\Models\Tenant\User::find($handledByUserId);
                                        if ($handledUser && $handledUser->employees) {
                                            $activeAssignment = $handledUser->employees->assignments()
                                                ->where(function ($q) {
                                                    $q->whereNull('end_date')
                                                      ->orWhereDate('end_date', '>=', now());
                                                })
                                                ->latest()
                                                ->first();
                                        }

                                        if (!$activeAssignment) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Gagal')
                                                ->body('Teknisi yang dipilih sudah tidak aktif atau tidak memiliki penugasan (assignment) cabang yang valid.')
                                                ->danger()
                                                ->send();
                                            return;
                                        }
                                    }

                                    $newRepairId = \Illuminate\Support\Facades\DB::transaction(function () use ($record, $originalRepair, $originalRepairDevice, $branch, $activeAssignment) {
                                        $repairService = new \App\Services\RepairService();
                                        $codeData = $repairService->generateRepairCode($branch, true);

                                        $newRepair = \App\Models\Tenant\Repair::create([
                                            'branch_id' => $branch->id,
                                            'client_id' => $originalRepair->client_id,
                                            'code' => $codeData['code'],
                                            'sequence' => $codeData['sequence'],
                                            'status' => \App\Enums\RepairStatus::Incoming,
                                            'created_by' => Auth::id(),
                                            'received_by' => Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician'])) ? null : Auth::id(),
                                        ]);

                                        $newDevice = \App\Models\Tenant\RepairDevice::create([
                                            'repair_id' => $newRepair->id,
                                            'device_type_id' => $originalRepairDevice->device_type_id,
                                            'brand_id' => $originalRepairDevice->brand_id,
                                            'model' => $originalRepairDevice->model,
                                            'color' => $originalRepairDevice->color,
                                            'serial_number' => $originalRepairDevice->serial_number,
                                            'imei' => $originalRepairDevice->imei,
                                            'problems' => 'Klaim Garansi: ' . $record->problems,
                                            'accessories' => $originalRepairDevice->accessories,
                                            'checklist' => $originalRepairDevice->checklist,
                                            'status' => \App\Enums\RepairDeviceStatus::Todo,
                                        ]);

                                        if ($activeAssignment) {
                                            $newDevice->technicians()->create([
                                                'employee_assignment_id' => $activeAssignment->id,
                                                'role' => 'primary',
                                            ]);
                                            
                                            // Update status to Checking AFTER assigning technician manually
                                            $newDevice->update(['status' => \App\Enums\RepairDeviceStatus::Checking]);
                                        }

                                        $record->update([
                                            'new_repair_id' => $newRepair->id,
                                            'status' => 'in_progress',
                                        ]);

                                        return $newRepair->id;
                                    });

                                    \Filament\Notifications\Notification::make()
                                        ->title('Berhasil')
                                        ->body('Tiket repair baru telah dibuat.')
                                        ->success()
                                        ->send();

                                    if ($isTechnician) {
                                        redirect()->to(\App\Filament\Tenant\Pages\TechnicianBoard::getUrl());
                                    } else {
                                        redirect()->to(\App\Filament\Tenant\Resources\Repairs\RepairResource::getUrl('index') . '?tableAction=edit&tableActionRecord=' . $newRepairId);
                                    }
                                }),
                            Action::make('process_refund')
                                ->label('Retur & Refund')
                                ->icon('heroicon-o-arrow-uturn-left')
                                ->color('danger')
                                ->modalHeading('Proses Retur Sparepart & Refund Uang Customer')
                                ->modalDescription('Proses ini akan mengembalikan uang ke pelanggan (tercatat di pengeluaran kas), membatalkan masa garansi (VOID), dan menyesuaikan stok sparepart.')
                                ->modalSubmitActionLabel('Konfirmasi Retur & Refund')
                                ->visible(fn ($record) => $record !== null && $record->status !== 'completed' && $record->status !== 'rejected' && empty($record->refund_payment_id))
                                ->schema(function ($record) {
                                    $item = $record?->repairItemWarranty?->repairItem;
                                    $origPrice = $item?->subtotal_item ?? $item?->price ?? 0;
                                    $productName = $item?->productVariant?->product?->name ?? '';
                                    $variantName = $item?->productVariant?->name ?? '';
                                    $fullItemName = trim($productName . ' ' . $variantName) ?: 'Sparepart / Servis';

                                    return [
                                        Placeholder::make('info_item')
                                            ->label('Item yang Diretur')
                                            ->content("{$fullItemName} (Harga Asal: Rp " . number_format($origPrice, 0, ',', '.') . ")"),
                                        \Filament\Forms\Components\TextInput::make('refund_amount')
                                            ->label('Nominal Pengembalian Dana (Refund)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default($origPrice)
                                            ->required()
                                            ->helperText('Nominal uang riil yang dikembalikan ke pelanggan. Dapat disesuaikan bila ada potongan biaya admin/cek.')
                                            ->minValue(0),
                                        \Filament\Forms\Components\Select::make('payment_method_id')
                                            ->label('Metode Pembayaran Keluar')
                                            ->options(\App\Models\Tenant\PaymentMethod::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Pilih akun/kas yang digunakan untuk mengeluarkan dana refund.'),
                                        \Filament\Forms\Components\Select::make('return_item_action')
                                            ->label('Perlakuan Fisik Sparepart Retur')
                                            ->options([
                                                'damaged' => 'Barang Rusak / Cacat (Disimpan untuk retur ke supplier / klaim vendor)',
                                                'restock' => 'Masuk Kembali ke Stok Gudang Siap-Jual (Restock +1)',
                                                'scrap' => 'Barang Dibuang / Limbah (Scrap)',
                                            ])
                                            ->default('damaged')
                                            ->required(),
                                        \Filament\Forms\Components\Textarea::make('refund_reason')
                                            ->label('Alasan Retur & Pengembalian Dana')
                                            ->required()
                                            ->default('Pelanggan meminta pengembalian dana karena sparepart bermasalah.')
                                            ->rows(2),
                                    ];
                                })
                                ->action(function ($record, array $data) {
                                    if (!$record) return;

                                    \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                        $warranty = $record->repairItemWarranty;
                                        $repairItem = $warranty?->repairItem;
                                        $repairDevice = $repairItem?->repairDevice;
                                        $repair = $repairDevice?->repair;

                                        if (!$repair) {
                                            throw new \Exception('Data perbaikan asal tidak ditemukan.');
                                        }

                                        $invoice = \App\Models\Tenant\RepairInvoice::findOrCreateForRepair($repair);

                                        // 1. Simpan pembayaran bertipe 'refund'
                                        $payment = \App\Models\Tenant\RepairPayment::create([
                                            'repair_invoice_id' => $invoice->id,
                                            'type' => 'refund',
                                            'payment_method_id' => $data['payment_method_id'],
                                            'reference_number' => 'REF-' . strtoupper(\Illuminate\Support\Str::random(6)),
                                            'amount' => (int) $data['refund_amount'],
                                            'refund_reason' => $data['refund_reason'],
                                            'payment_date' => now(),
                                            'created_by' => \Illuminate\Support\Facades\Auth::id(),
                                        ]);

                                        // 2. Perlakuan fisik sparepart
                                        if ($data['return_item_action'] === 'restock' && $repairItem && $repairItem->product_variant_id) {
                                            $branchId = $repair->branch_id ?? session('current_branch_id');
                                            $variant = $repairItem->productVariant;
                                            if ($variant) {
                                                \App\Models\Tenant\StockMovement::create([
                                                    'branch_id' => $branchId,
                                                    'product_id' => $variant->product_id,
                                                    'product_variant_id' => $variant->id,
                                                    'direction' => 'in',
                                                    'quantity' => $repairItem->qty ?? 1,
                                                    'note' => 'Restock barang retur klaim garansi #' . $record->id,
                                                    'source_type' => get_class($record),
                                                    'source_id' => $record->id,
                                                ]);

                                                $stock = \App\Models\Tenant\Stock::firstOrNew([
                                                    'branch_id' => $branchId,
                                                    'product_id' => $variant->product_id,
                                                    'product_variant_id' => $variant->id,
                                                ], ['stock' => 0]);
                                                $stock->stock += ($repairItem->qty ?? 1);
                                                $stock->save();
                                            }
                                        }

                                        // 3. Batalkan masa garansi item (VOID)
                                        if ($warranty) {
                                            $warranty->update([
                                                'status' => 'void',
                                                'notes' => trim(($warranty->notes ?? '') . "\n[Sistem] Garansi dinonaktifkan (VOID) karena item telah diretur & di-refund sebesar Rp " . number_format($data['refund_amount'], 0, ',', '.')),
                                            ]);
                                        }

                                        // 4. Update status klaim menjadi completed
                                        $actionText = match ($data['return_item_action']) {
                                            'restock' => 'Fisik barang di-restock ke gudang',
                                            'scrap' => 'Fisik barang dibuang (scrap)',
                                            default => 'Fisik barang ditandai rusak untuk klaim supplier',
                                        };

                                        $record->update([
                                            'refund_amount' => $data['refund_amount'],
                                            'refund_payment_id' => $payment->id,
                                            'return_item_action' => $data['return_item_action'],
                                            'status' => 'completed',
                                            'solution' => 'Retur & Refund disetujui sebesar Rp ' . number_format($data['refund_amount'], 0, ',', '.') . " ({$actionText}).",
                                            'notes' => trim(($record->notes ?? '') . "\n[Refund] " . $data['refund_reason']),
                                        ]);
                                    });

                                    \Filament\Notifications\Notification::make()
                                        ->title('Retur & Refund Berhasil Diproses')
                                        ->body('Dana pengembalian sebesar Rp ' . number_format($data['refund_amount'], 0, ',', '.') . ' telah dicatat dan garansi telah dinonaktifkan (VOID).')
                                        ->success()
                                        ->send();
                                }),
                            Action::make('take_job')
                                ->label('Saya Kerjakan')
                                ->icon('heroicon-m-hand-raised')
                                ->color('primary')
                                ->action(function (Set $set) {
                                    $set('handled_by', Auth::id());
                                    $set('status', 'in_progress');
                                })
                                ->visible(fn (Get $get) => $get('status') === 'pending' && (Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician'])) )),
                            Action::make('finish_job')
                                ->label('Selesaikan Pekerjaan')
                                ->icon('heroicon-m-check-circle')
                                ->color('success')
                                ->action(function (Set $set) {
                                    $set('status', 'completed');
                                })
                                ->visible(fn (Get $get) => $get('status') === 'in_progress' && $get('handled_by') == Auth::id() && (Auth::user()->hasRole(settingTenant('tenant_technical_roles', ['technician'])) )),
                        ]),

                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([
                                Textarea::make('solution')
                                    ->label('Solusi / Tindakan'),
                                Textarea::make('notes')
                                    ->label('Catatan Internal'),
                            ]), 
                    ])->columns(3),

                Section::make('Rincian Retur & Pengembalian Dana (Refund)')
                    ->visible(fn ($record) => !empty($record?->refund_payment_id))
                    ->schema([
                        Placeholder::make('refund_amount_display')
                            ->label('Nominal Refund')
                            ->content(fn ($record) => 'Rp ' . number_format($record?->refund_amount ?? 0, 0, ',', '.')),
                        Placeholder::make('refund_method_display')
                            ->label('Metode Pengeluaran Kas')
                            ->content(fn ($record) => $record?->refundPayment?->paymentMethod?->name ?? '-'),
                        Placeholder::make('return_action_display')
                            ->label('Status Fisik Barang')
                            ->content(function ($record) {
                                return match ($record?->return_item_action) {
                                    'restock' => 'Dikembalikan ke Stok Gudang (Restock)',
                                    'scrap' => 'Dibuang / Limbah (Scrap)',
                                    default => 'Barang Rusak (Disiapkan untuk Supplier)',
                                };
                            }),
                        Placeholder::make('refund_reason_display')
                            ->label('Alasan Retur')
                            ->content(fn ($record) => $record?->refundPayment?->refund_reason ?? '-'),
                    ])->columns(4),
            ]);
    }
}
