<?php

namespace App\Filament\Tenant\Resources\Repairs\Helpers;

use App\Models\Tenant\Repair;
use Filament\Actions\Action;
use Filament\Schemas\Components\Form;
use Illuminate\Support\Facades\DB;

class RepairFormLogic
{
    public static function mutateDataBeforeCreate(array $data): array
    {
        $branch = \App\Models\Tenant\Branch::find($data['branch_id'] ?? null);
        if ($branch) {
            $result = app(\App\Services\RepairService::class)->generateRepairCode($branch, true);
            $data['sequence'] = $result['sequence'];
            $data['code'] = $result['code'];
        }
        $data['created_by'] = \Illuminate\Support\Facades\Auth::id();
        $data['status'] = \App\Enums\RepairStatus::cases()[0]->value;

        return $data;
    }

    public static function mapRepairItemsForForm($repairItems): array
    {
        return collect($repairItems)->mapWithKeys(function ($item) {
            $itemArr = $item->toArray();
            $record = $item->productVariant;

            if ($record) {
                $p = $record->product;
                // dd($itemArr);
                $brand = $p?->brand?->name ? "{$p->brand->name} " : '';
                $name = $brand . ($p?->name ?? '-') . ($record->name && $p?->has_variant ? " - {$record->name}" : '');

                $unit = $p?->warranty_unit?->value ?? $p?->warranty_unit;
                $warranty = $p?->warranty_value ? "{$p->warranty_value} {$unit}" : 'Tidak ada';

                $itemArr['name'] = $name;
                $itemArr['warranty'] = $warranty;
                $itemArr['has_sn'] = (bool) $p?->has_sn;
                $itemArr['product_id'] = $p?->id;

                if ($p?->has_sn) {
                    $serial = $item->stockMovement?->productSerials->first();
                    $itemArr['sn'] = $serial ? $serial->serial_number : null;
                    $itemArr['product_serial_id'] = $serial ? $serial->id : null;
                } else {
                    $itemArr['sn'] = 'Non-SN';
                    $itemArr['product_serial_id'] = null;
                }
            } else {
                $itemArr['name'] = '-';
                $itemArr['warranty'] = 'Tidak ada';
                $itemArr['has_sn'] = false;
                $itemArr['sn'] = 'Non-SN';
            }

            return [(string) \Illuminate\Support\Str::uuid() => $itemArr];
        })->toArray();
    }

    public static function getDokumenGambarAction(?\Filament\Schemas\Components\Utilities\Get $parentGet = null)
    {
        return
            \Filament\Actions\Action::make('dokumen_gambar')
            ->label('Dokumentasi Foto')
            ->icon('heroicon-o-photo')
            // ->fillForm(fn(\Filament\Schemas\Components\Utilities\Get $get) => [
            //     'gallery' => $get('repair_photos') ?? [],
            // ])

            ->modalSubmitAction(fn(Action $action, string $operation) => $action->hidden($operation == 'view'))
            ->fillForm(function (\Filament\Schemas\Components\Utilities\Get $get, string $operation) {
                // Debugging di sini: akan muncul saat tombol action diklik dan modal akan terbuka
                // dd($get('repair_photos'));

                return [
                    'gallery' => $get('repair_photos') ?? [],
                    '_is_view' => $operation === 'view',
                ];
            })
            ->action(function (array $data, \Filament\Schemas\Components\Utilities\Set $set) {
                // dd($data['gallery']);
                $set('repair_photos', $data['gallery'] ?? []);
            })
            ->schema([
                \Filament\Forms\Components\Hidden::make('_is_view'),
                \Filament\Forms\Components\Repeater::make('gallery')
                    ->label('Galeri Foto Servis')
                    ->disabled(fn(\Filament\Schemas\Components\Utilities\Get $get) => (bool) $get('_is_view'))
                    ->schema([
                        \Filament\Forms\Components\Hidden::make('taken_by')
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
                        \Filament\Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options(app(\App\Services\DatabaseService::class)->getEnumValues('repair_photos', 'type'))
                            ->default('before')
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('path')
                            ->image()
                            ->label('Foto')
                            ->multiple()
                            ->openable()
                            ->downloadable()
                            ->required()
                            ->directory(function (?\Illuminate\Database\Eloquent\Model $record) use ($parentGet) {
                                $code = null;

                                if ($record instanceof \App\Models\Tenant\RepairDevice) {
                                    $code = $record->repair?->code;
                                } elseif ($record instanceof \App\Models\Tenant\Repair) {
                                    $code = $record->code;
                                }

                                if (empty($code) && $parentGet) {
                                    $code = $parentGet('code');
                                }

                                if (empty($code)) {
                                    return 'images/repair_photos/temp/';
                                }
                                return 'images/repair_photos/' . $code . '/';
                            })
                            ->getUploadedFileNameForStorageUsing(
                                fn(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file, callable $get): string => $get('type') . '-' . now()->timestamp . '.' . $file->getClientOriginalExtension(),
                            )
                            ->extraInputAttributes([
                                'capture' => 'environment',
                            ]),
                    ])
                    ->grid(3)
                    ->addActionLabel('Tambah Foto Baru')
                    ->collapsible()
            ])
            ->modalHeading('Dokumentasi Perbaikan')
            ->modalWidth('4xl');
    }

    public static function getChecklistRepeater()
    {
        return \YousefAman\ModalRepeater\ModalRepeater::make('checklist')
            ->live()
            ->modalColumns(7)
            ->modalWidth('3xl')
            ->editAction(
                fn(Action $action) => $action
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->disabled(fn(string $operation) => $operation === 'view')
            )
            ->default(function (\Filament\Schemas\Components\Utilities\Get $get, string $operation) {
                if ($operation !== 'create') {
                    return [];
                }

                $deviceTypeId = $get('device_type_id') ?? 1;
                return \App\Models\Tenant\ChecklistTemplate::query()
                    ->where('device_type_id', $deviceTypeId)
                    ->where('is_active', true)
                    ->get()
                    ->map(fn($template) => [
                        'checklist_template_id' => $template->id,
                        'name' => $template->name,
                        'status' => null,
                        'note' => null,
                    ])
                    ->toArray();
            })
            ->tableColumns([
                \YousefAman\ModalRepeater\Column::make('name'),
                \YousefAman\ModalRepeater\Column::make('status'),
                \YousefAman\ModalRepeater\Column::make('note'),
            ])
            ->schema([
                \Filament\Forms\Components\Hidden::make('checklist_template_id'),

                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Item')
                    ->columnSpan(2)
                    ->dehydrated(),

                \Filament\Forms\Components\ToggleButtons::make('status')
                    ->label('Status')
                    ->columnSpan(3)
                    ->options([
                        'normal' => 'Normal',
                        'damaged' => 'Rusak',
                        'missing' => 'Tidak Ada',
                    ])
                    ->colors([
                        'normal' => 'success',
                        'damaged' => 'danger',
                        'missing' => 'warning',
                    ])
                    ->inline(),
                \Filament\Forms\Components\TextInput::make('note')
                    ->label('Catatan')
                    ->columnSpan(2),
            ]);
    }

    public static function getTechniciansRepeater(?\Filament\Schemas\Components\Utilities\Get $parentGet = null, bool $isBoard = false)
    {
        return \Filament\Forms\Components\Repeater::make('technicians')
            ->when(!$isBoard, fn($repeater) => $repeater->relationship('technicians')->visible(fn(\Filament\Schemas\Components\Utilities\Get $get) => filled($get('id')) && !empty($get('technicians'))))
            ->table([
                \Filament\Forms\Components\Repeater\TableColumn::make('Nama'),
                \Filament\Forms\Components\Repeater\TableColumn::make('Peran'),
            ])
            ->compact()
            // ->columns($isBoard ? 2 : 1)
            // ->columnSpan($isBoard ? 2 : 7)
            ->defaultItems(0)
            ->deleteAction(
                fn(\Filament\Actions\Action $action) => $action->hidden(
                    function (array $arguments, \Filament\Forms\Components\Repeater $component): bool {
                        if (empty($arguments['item'])) {
                            return false;
                        }
                        $state = $component->getState();
                        if (!isset($state[$arguments['item']])) {
                            return false;
                        }
                        return ($state[$arguments['item']]['role'] ?? null) === 'primary';
                    }
                )
            )
            ->dehydrated(true)
            ->addActionLabel($isBoard ? 'Tambah Teknisi' : 'Tambah Teknisi (Asisten/QC)')
            ->schema([
                \Filament\Forms\Components\Select::make('employee_assignment_id')
                    ->label('Teknisi')
                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get) use ($parentGet) {
                        return \App\Filament\Tenant\Resources\Repairs\Schemas\RepairFormDevice::getActiveTechniciansOptions($get, $parentGet);
                    })
                    ->searchable()
                    ->live()
                    ->disableOptionWhen(function (string $value, $state, \Filament\Schemas\Components\Utilities\Get $get) {
                        $selected = collect($get('../../technicians'))->pluck('employee_assignment_id')->filter()->toArray();
                        return in_array($value, $selected) && $value != $state;
                    })
                    ->required(fn(\Filament\Schemas\Components\Utilities\Get $get): bool => $get('role') !== 'primary')
                    ->disabled(fn(\Filament\Schemas\Components\Utilities\Get $get): bool => $get('role') === 'primary')
                    ->dehydrated(),
                \Filament\Forms\Components\Select::make('role')
                    ->label('Peran')
                    ->options(app(\App\Services\DatabaseService::class)->getEnumValues('repair_technicians', 'role'))
                    ->default('assistant')
                    ->live()
                    ->disableOptionWhen(function (string $value, $state, \Filament\Schemas\Components\Utilities\Get $get) {
                        $selected = collect($get('../../technicians'))->pluck('role')->filter()->toArray();
                        return in_array($value, $selected) && $value != $state;
                    })
                    ->required(fn(\Filament\Schemas\Components\Utilities\Get $get): bool => $get('role') !== 'primary')
                    ->disabled(fn(\Filament\Schemas\Components\Utilities\Get $get): bool => $get('role') === 'primary')
                    ->dehydrated(),
            ])
            // ->when($isBoard, fn($repeater) => $repeater->columns(2))
        ;
    }

    /**
     * Logic mutate data sebelum form diisi saat Edit.
     */
    public static function mutateDataBeforeFill(array $data, Repair $record): array
    {
        $data['multiple_devices'] = $record->repairDevices->count() > 1;
        $data['add_items'] = $record->repairDevices()->whereHas('repairItems')->exists()
            || $record->repairInvoice()->exists();
        // dd($data['add_items']);
        if ($invoice = $record->repairInvoice) {
            $data['invoice_number'] = $invoice->invoice_number;
            $data['invoice_date'] = $invoice->invoice_date;
        }

        // dd($data);
        return $data;
    }

    /**
     * Logic setelah form disimpan (bisa dipakai saat Create & Edit).
     */
    public static function afterSave(array $data, Repair $record): void
    {
        DB::transaction(function () use ($data, $record) {
            // $addItems = $data['add_items'] ?? false;
            $addItems = collect($data['repair_devices'] ?? [])
                ->contains(fn($device) => !empty($device['repair_items']));

            if (!$addItems) {
                // Jika tidak addItems, hapus item (trigger model event untuk recalibrate ke 0 atau hapus invoice)
                $record->repairDevices->each(function ($device) {
                    $device->repairItems()->get()->each->delete();
                });
            }


            // Simpan repair_photos (selalu) dan repair_items secara manual
            $deviceModels = $record->repairDevices()->orderBy('id')->get();
            $devicesData = array_values($data['repair_devices'] ?? []);

            // Pisahkan mana device yang sudah ada dan yang baru
            $existingDeviceIds = collect($devicesData)->pluck('id')->filter()->toArray();
            $newDeviceModels = $deviceModels->whereNotIn('id', $existingDeviceIds)->values();

            $newIndex = 0;
            foreach ($devicesData as $deviceData) {
                // dd($deviceData);
                $deviceModel = null;
                if (!empty($deviceData['id'])) {
                    $deviceModel = $deviceModels->firstWhere('id', $deviceData['id']);
                } else {
                    $deviceModel = $newDeviceModels[$newIndex] ?? null;
                    $newIndex++;
                }

                if ($deviceModel) {
                    // Sync repair_photos (selalu)
                    $photos = $deviceData['repair_photos'] ?? [];
                    $existingPhotoIds = collect($photos)->pluck('id')->filter()->toArray();
                    // dd($photos);
                    $deviceModel->repairPhotos()->whereNotIn('id', $existingPhotoIds)->delete();

                    foreach ($photos as $photo) {
                        $photoData = \Illuminate\Support\Arr::except($photo, ['created_at', 'updated_at']);

                        // Hilangkan UUID keys bawaan Filament saat multiple upload,
                        // sehingga tersimpan sebagai JSON Array murni: ["images/1.png", "images/2.png"]
                        if (isset($photoData['path']) && is_array($photoData['path'])) {
                            $photoData['path'] = array_values($photoData['path']);
                        }

                        if (!empty($photo['id'])) {
                            // Menggunakan Eloquent update agar event/trait model (seperti HasDeletableFiles) berjalan
                            $deviceModel->repairPhotos()->find($photo['id'])?->update($photoData);
                        } else {
                            $deviceModel->repairPhotos()->create($photoData);
                        }
                    }

                    // Sync repair_items (hanya jika addItems true)
                    if ($addItems) {
                        $items = $deviceData['repair_items'] ?? [];
                        self::syncRepairItems($deviceModel, $items, $record->branch_id, $record->code);
                    } else {
                        // Jika addItems false, pastikan hapus items melalui event agar stock movement ikut terhapus
                        $deviceModel->repairItems()->get()->each->delete();
                    }

                    // Paksa status jadi Checking jika tiket di-assign teknisi langsung oleh CS dari awal
                    if ($deviceModel->status === \App\Enums\RepairDeviceStatus::Todo) {
                        if ($deviceModel->technicians()->exists()) {
                            $deviceModel->update(['status' => \App\Enums\RepairDeviceStatus::Checking]);
                        }
                    }
                }
            }

            // Proses invoice_photo jika ada di form
            if (!empty($data['invoice_photo'])) {
                if (is_array($data['invoice_photo'])) {
                    foreach ($data['invoice_photo'] as $photoPath) {
                        self::processInvoicePhoto($photoPath, $record, 'dropoff');
                    }
                } else {
                    self::processInvoicePhoto($data['invoice_photo'], $record, 'dropoff');
                }
            }
        });
    }

    public static function syncRepairItems(\App\Models\Tenant\RepairDevice $deviceModel, array $items, $branchId, $repairCode = null): void
    {
        $existingItemIds = collect($items)->pluck('id')->filter()->toArray();
        $deviceModel->repairItems()->whereNotIn('id', $existingItemIds)->get()->each->delete();

        foreach ($items as $item) {
            $itemData = \Illuminate\Support\Arr::only($item, app(\App\Models\Tenant\RepairItem::class)->getFillable());

            $createdRepairItem = null;
            if (!empty($item['id'])) {
                $createdRepairItem = $deviceModel->repairItems()->find($item['id']);
                if ($createdRepairItem) {
                    $createdRepairItem->update($itemData);
                }
            } else {
                $createdRepairItem = $deviceModel->repairItems()->create($itemData);
            }

            // Catat pergerakan stok jika ini adalah produk (sparepart) bukan sekadar jasa tanpa produk
            if ($createdRepairItem && !empty($item['product_id']) && !empty($item['product_variant_id'])) {
                $serialIds = !empty($item['product_serial_id']) ? [$item['product_serial_id']] : [];

                \App\Services\Stock\StockMovementService::updateOrCreate(
                    $createdRepairItem,
                    [
                        'branch_id' => $branchId,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'],
                        'direction' => 'out',
                        'quantity' => $item['qty'] ?? 1,
                        'note' => 'Penggunaan sparepart untuk reparasi ' . ($repairCode ?? ''),
                    ],
                    $serialIds
                );
            }
        }
    }
    public static function processInvoicePhoto(string $originalPath, Repair $record, string $type = 'pickup'): void
    {
        if (empty($originalPath)) return;

        // Membuat nama kustom
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION) ?: 'jpg';
        $customFilename = 'handover-' . $type . '-' . now()->timestamp . '.' . $extension;
        $newPath = 'images/repair_photos/' . $record->code . '/' . $customFilename;

        // Rename file fisik
        if (\Illuminate\Support\Facades\Storage::exists($originalPath)) {
            \Illuminate\Support\Facades\Storage::move($originalPath, $newPath);
        } else {
            dd($originalPath, $newPath);
            $newPath = $originalPath; // fallback jika gagal pindah
        }

        \App\Models\Tenant\RepairHandover::updateOrCreate(
            [
                'repair_id' => $record->id,
                'type' => $type,
            ],
            [
                'photo_path' => $newPath,
            ]
        );
    }
}
