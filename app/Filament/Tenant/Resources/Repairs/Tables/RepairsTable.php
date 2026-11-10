<?php

namespace App\Filament\Tenant\Resources\Repairs\Tables;

use App\Enums\RepairDeviceStatus;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use App\Enums\RepairStatus;
use App\Enums\TenantFeature;
use App\Filament\Tenant\Resources\Repairs\Actions\MakePayment;
use App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic;
use App\Models\Tenant\Repair;
use emmanpbarrameda\FilamentTakePictureField\FilamentTakePictureField;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentAutograph\Forms\Components\Enums\DownloadableFormat;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class RepairsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code'),
                TextColumn::make('branch.name')
                    ->visible(Auth::user()->hasRole(settingTenant('tenant_bypass_roles', []))),
                TextColumn::make('client.name')
                    ->action(\App\Filament\Tenant\Resources\Clients\Actions\ClientHistoryAction::make(Action::class, 'client_history_modal')),
                TextColumn::make('devices')
                    ->label('Perangkat')
                    ->state(function (Repair $record): array {
                        return $record->repairDevices->map(fn($device) => trim($device->brand?->name . ' ' . $device->model))->filter()->toArray();
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
                        return $colors[crc32($state) % count($colors)];
                    }),
                TextColumn::make('technicians')
                    ->label('Teknisi')
                    ->state(function (Repair $record): array {
                        $techs = [];
                        foreach ($record->repairDevices as $device) {
                            foreach ($device->technicians as $tech) {
                                $name = $tech->employeeAssignment?->employee?->name ?? 'Unknown';
                                $firstName = explode(' ', trim($name ?: 'Unknown'))[0];
                                $role = ucfirst($tech->role);
                                $techs[] = "{$firstName} - {$role}";
                            }
                        }
                        return array_unique($techs);
                    }),
                TextColumn::make('receivedBy.name')->label('Diterima Oleh'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->state(function (Repair $record): string {
                        $invoice = $record->repairInvoice;
                        if (! $invoice || $invoice->grand_total == 0) return 'Belum Dihitung';
                        if ($invoice->status === 'refunded') return 'Refunded';
                        if ((float)$invoice->remaining_amount <= 0 && $invoice->grand_total > 0) return 'Lunas';
                        if ((float)$invoice->paid_amount > 0) {
                            return 'Kurang Rp ' . number_format($invoice->remaining_amount, 0, ',', '.');
                        }
                        return 'Belum Bayar (Rp ' . number_format($invoice->remaining_amount, 0, ',', '.') . ')';
                    })
                    ->badge()
                    ->color(function (Repair $record): string {
                        $invoice = $record->repairInvoice;
                        if (! $invoice || $invoice->grand_total == 0) return 'gray';
                        if ($invoice->status === 'refunded') return 'danger';
                        if ((float)$invoice->remaining_amount <= 0 && $invoice->grand_total > 0) return 'success';
                        if ((float)$invoice->paid_amount > 0) return 'warning';
                        return 'danger';
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\RepairStatus::class)
                    ->label('Status'),
                // \Filament\Tables\Filters\SelectFilter::make('client_id')
                //     ->relationship('client', 'name')
                //     ->label('Klien')
                //     ->searchable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    ViewAction::make()
                        ->slideOver()
                        ->modalWidth('7xl')
                        ->mutateRecordDataUsing(function (array $data, Repair $record): array {
                            return \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mutateDataBeforeFill($data, $record);
                        }),
                    EditAction::make()->slideOver()
                        ->modalWidth('7xl')




                        // DEBUGGING
                        ->hidden(fn(Repair $record) => $record->status === RepairStatus::Delivered || $record->status === RepairStatus::Cancel)
                        ->mutateRecordDataUsing(function (array $data, Repair $record): array {
                            return \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mutateDataBeforeFill($data, $record);
                        })
                        // ->before(function (array $data, Repair $record) {
                        //     dd('before edit',$data);
                        //     // return \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mutateDataBeforeSave($data, $record);
                        // })
                        ->after(function (array $data, Repair $record) {
                            // dd('after edit', $data);

                            RepairFormLogic::afterSave($data, $record);
                        }),

                    Action::make('viewCommissions')
                        ->label('Lihat Komisi')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->modalHeading('Komisi Teknisi')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->visible(fn() => Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['owner'])))
                        ->schema([
                            \Filament\Infolists\Components\RepeatableEntry::make('commissions')
                                ->label('')
                                ->getStateUsing(function (Repair $record) {
                                    $data = \App\Models\Tenant\RepairTechnicianCommission::whereHas('repairTechnician.repairDevice', function ($q) use ($record) {
                                        $q->where('repair_id', $record->id);
                                    })->with(['repairTechnician.employeeAssignment.employee.user', 'compensationComponent'])->get();

                                    // dd($record, $data);
                                    return $data;
                                })
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('repairTechnician.employeeAssignment.employee.user.name')
                                        ->label('Teknisi'),
                                    \Filament\Infolists\Components\TextEntry::make('compensationComponent.name')
                                        ->label('Jenis Komisi'),
                                    \Filament\Infolists\Components\TextEntry::make('notes')
                                        ->label('Keterangan'),
                                    \Filament\Infolists\Components\TextEntry::make('amount')
                                        ->label('Nominal')
                                        ->money('IDR'),
                                ])
                                ->columns(4)
                        ]),

                    Action::make('viewWarranties')
                        ->label('Lihat Garansi')
                        ->icon('heroicon-o-shield-check')
                        ->color('info')
                        ->modalHeading('Garansi Item / Servis')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->schema([
                            \Filament\Infolists\Components\RepeatableEntry::make('warranties')
                                ->label('')
                                ->getStateUsing(function (Repair $record) {
                                    return \App\Models\Tenant\RepairItemWarranty::whereHas('repairItem.repairDevice', function ($q) use ($record) {
                                        $q->where('repair_id', $record->id);
                                    })->with(['repairItem.productVariant.product'])->get();
                                })
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('repairItem.productVariant.product.name')
                                        ->label('Produk/Servis'),
                                    \Filament\Infolists\Components\TextEntry::make('repairItem.productVariant.name')
                                        ->label('Varian'),
                                    \Filament\Infolists\Components\TextEntry::make('start_date')
                                        ->label('Mulai')
                                        ->date(),
                                    \Filament\Infolists\Components\TextEntry::make('end_date')
                                        ->label('Berakhir')
                                        ->date(),
                                    \Filament\Infolists\Components\TextEntry::make('end_date_cooldown')
                                        ->label('Sisa Waktu')
                                        ->state(fn($record) => $record->end_date)
                                        ->since()
                                        ->badge()
                                        ->color(fn($record) => $record->end_date && \Carbon\Carbon::parse($record->end_date)->isPast() ? 'danger' : 'success'),
                                    \Filament\Infolists\Components\TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'active' => 'success',
                                            'expired' => 'danger',
                                            'void' => 'danger',
                                            default => 'gray',
                                        }),
                                    Actions::make([
                                        Action::make('claimWarranty')
                                            ->label('Klaim')
                                            ->icon('heroicon-o-wrench-screwdriver')
                                            ->color('warning')
                                            ->modalHeading('Form Klaim Garansi')
                                            ->schema([
                                                \Filament\Forms\Components\Textarea::make('problems')
                                                    ->label('Keluhan / Masalah')
                                                    ->required(),
                                            ])
                                            ->visible(function (\App\Models\Tenant\RepairItemWarranty $record) {
                                                return $record->status === 'active'
                                                    && $record->end_date
                                                    && \Carbon\Carbon::parse($record->end_date)->isFuture()
                                                    && !$record->repairWarrantyClaims()->whereIn('status', ['pending', 'in_progress'])->exists();
                                            })
                                            ->action(function (array $data, \App\Models\Tenant\RepairItemWarranty $record) {
                                                $warranty = $record->load('repairItem.repairDevice.technicians.employeeAssignment.employee');
                                                $originalUserId = $warranty->repairItem?->repairDevice?->technicians?->first()?->employeeAssignment?->employee?->user_id ?? Auth::id();

                                                \App\Models\Tenant\RepairWarrantyClaim::updateOrCreate([
                                                    'repair_item_warranty_id' => $record->id,
                                                    'status' => 'pending',
                                                ], [
                                                    'claim_date' => now(),
                                                    'problems' => $data['problems'],
                                                    'handled_by' => $originalUserId,
                                                ]);

                                                \Filament\Notifications\Notification::make()
                                                    ->title('Klaim Garansi Berhasil Diajukan')
                                                    ->success()
                                                    ->send();
                                            }),
                                    ]),
                                ])
                                ->columns(7)
                        ])
                        ->visible(function (Repair $record) {
                            return \App\Models\Tenant\RepairItemWarranty::whereHas('repairItem.repairDevice', function ($q) use ($record) {
                                $q->where('repair_id', $record->id);
                            })->exists();
                        }),

                    Action::make('whatsapp')
                        ->label('Kirim WA')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->visible(fn (Repair $record): bool => !empty($record->client?->whatsapp ?? $record->client?->phone))
                        ->url(function (Repair $record) {
                            // Ambil nomor WA atau Phone
                            $phone = $record->client->whatsapp ?? $record->client->phone;

                            // Bersihkan karakter selain angka
                            $phone = preg_replace('/[^0-9]/', '', (string) $phone);

                            // Ubah awalan 0 menjadi 62
                            if (str_starts_with($phone, '0')) {
                                $phone = '62' . substr($phone, 1);
                            }

                            $token = \Vinkla\Hashids\Facades\Hashids::encode($record->id);
                            $url = route('client.repair.status', ['token' => $token]);
                            $deviceNames = $record->repairDevices->map(fn($d) => trim($d->brand?->name . ' ' . $d->model))->implode(', ');
                            $deviceNames = $deviceNames ?: 'Perangkat'; // Fallback jika tidak ada device

                            // Template Pesan berdasarkan Status
                            $message = match ($record->status?->value) {
                                'pending' => "Halo {$record->client->name},\n\nPerangkat *{$deviceNames}* Anda telah kami terima dengan nomor tiket *{$record->code}*.\n\nAnda dapat memantau status perbaikan melalui link berikut:\n{$url}\n\n_(Gunakan nomor tiket dan nomor HP ini untuk login)_",

                                'in_progress' => "Halo {$record->client->name},\n\nPerangkat *{$deviceNames}* Anda (Tiket: *{$record->code}*) saat ini sedang dalam proses pengerjaan oleh teknisi kami.\n\nPantau progresnya di:\n{$url}",

                                'completed' => "Halo {$record->client->name},\n\nKabar gembira! Perangkat *{$deviceNames}* Anda (Tiket: *{$record->code}*) sudah selesai diperbaiki dan siap untuk diambil.\n\nCek rincian nota dan detailnya di:\n{$url}",

                                default => "Halo {$record->client->name},\n\nBerikut adalah update untuk perbaikan perangkat *{$deviceNames}* Anda (Tiket: *{$record->code}*).\n\nCek detail statusnya di:\n{$url}"
                            };

                            return "https://wa.me/{$phone}?text=" . urlencode($message);
                        })
                        ->openUrlInNewTab(),

                    Action::make('print')
                        ->label('Cetak Nota')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn(Repair $record) => route('tenant.repairs.print', $record))
                        ->openUrlInNewTab(),

                    MakePayment::configure(Action::make('payment')),

                    Action::make('mark_as_delivered')
                        ->label('Serahkan Unit')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->modalHeading('Serahkan Perangkat ke Pelanggan')
                        ->modalDescription('Pastikan perangkat telah diserahkan dan diterima dengan baik oleh pelanggan. Anda dapat menambahkan foto bukti pengambilan (opsional).')
                        ->visible(fn(Repair $record) => $record->status === \App\Enums\RepairStatus::ReadyToPickup)
                        ->mountUsing(function ($form, Repair $record) {
                            $invoice = $record->repairInvoice;
                            if ($invoice && $invoice->remaining_amount > 0) {
                                Notification::make()
                                    ->title('Gagal Diserahkan (Belum Lunas)')
                                    ->body('Pelanggan masih memiliki sisa tagihan sebesar Rp ' . number_format($invoice->remaining_amount, 0, ',', '.'))
                                    ->danger()
                                    ->send();
                                throw new Halt();
                            }
                            $form->fill();
                        })
                        ->schema([
                            TextInput::make('recipient_name')
                                ->label('Nama Penerima')
                                ->required()
                                // ->datalist(fn() => \App\Models\Tenant\Client::pluck('name')->filter()->unique()->values()->toArray())
                                ->datalist(fn(Repair $record) => [$record->client->name]),

                            TakePicture::make('invoice_photo')
                                ->label('Foto Bukti Serah Terima / Pengambilan')
                                // ->multiple([
                                //     ['key' => 'main', 'label' => 'Main Picture'],
                                //     ['key' => 'front', 'label' => 'Front Picture'],
                                //     ['key' => 'left', 'label' => 'Left Side Picture'],
                                //     ['key' => 'right', 'label' => 'Right Side Picture'],
                                //     ['key' => 'back', 'label' => 'Back Picture'],
                                // ])
                                ->disk('')
                                // ->disk('public')
                                ->directory('images/repair_photos/temp'),

                            SignaturePad::make('signature')
                                ->label(__('Sign here'))
                                ->dotSize(2.0)
                                ->filename('autograph')             // Filename of the downloaded file (defaults to 'signature')
                                ->downloadable()                    // Allow download of the signature (defaults to false)
                                ->downloadableFormats([             // Available formats for download (defaults to all)
                                    DownloadableFormat::PNG,
                                    DownloadableFormat::JPG,
                                    DownloadableFormat::SVG,
                                ])
                                ->downloadActionDropdownPlacement('center-end')     // Dropdown placement of the download action (defaults to 'bottom-end')
                                ->confirmable()


                            // ->lineMinWidth(0.5)
                            // ->lineMaxWidth(2.5)
                            // ->throttle(16)
                            // ->minDistance(5)
                            // ->velocityFilterWeight(0.7)
                        ])
                        ->action(function (array $data, Repair $record) {
                            // dd($data);
                            if (!empty($data['invoice_photo'])) {
                                \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::processInvoicePhoto($data['invoice_photo'], $record, 'pickup');
                            }

                            if (!empty($data['signature'])) {
                                \App\Models\Tenant\RepairHandover::updateOrCreate(
                                    [
                                        'repair_id' => $record->id,
                                        'type' => 'pickup',
                                    ],
                                    [
                                        'recipient_name' => $data['recipient_name'],
                                        'signature' => $data['signature'],
                                    ]
                                );
                            }

                            $record->repairDevices()->update([
                                'status' => RepairDeviceStatus::Done,
                            ]);

                            $record->update([
                                'status' => \App\Enums\RepairStatus::Delivered,
                                'delivered_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Berhasil')
                                ->body('Status perbaikan telah diubah menjadi Delivered (Diserahkan).')
                                ->success()
                                ->send();
                        }),


                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
