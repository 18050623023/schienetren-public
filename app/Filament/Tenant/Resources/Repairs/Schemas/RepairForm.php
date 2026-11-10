<?php

namespace App\Filament\Tenant\Resources\Repairs\Schemas;

use App\Enums\Color;
use App\Enums\RepairStatus;
use App\Filament\Tenant\Resources\Branches\BranchResource;
use App\Filament\Tenant\Resources\Clients\ClientResource;
use App\Models\Tenant\Branch;
use App\Models\Tenant\ChecklistTemplate;
use App\Services\RepairService;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use YousefAman\ModalRepeater\Column;
use YousefAman\ModalRepeater\ModalRepeater;

// use function Laravel\Prompts\form;

// use YousefAman\ModalRepeater\ModalRepeater;

class RepairForm
{
    public static function configure(Schema $schema): Schema
    {
        $mutateRepairDeviceData = function (array $data) {
            if (!empty($data['id'])) {
                $device = \App\Models\Tenant\RepairDevice::with(['repairPhotos', 'repairItems.productVariant.product.brand', 'repairItems.stockMovement.productSerials'])->find($data['id']);
                if ($device) {
                    $data['repair_photos'] = $device->repairPhotos->mapWithKeys(function ($photo) {
                        $photoArr = $photo->toArray();
                        if (isset($photoArr['path'])) {
                            // Coba decode jika masih berupa string mentah
                            if (is_string($photoArr['path'])) {
                                $decoded = json_decode($photoArr['path'], true);
                                $photoArr['path'] = is_array($decoded) ? $decoded : [$photoArr['path']];
                            }

                            // Pastikan data dikirim ke form sebagai index array biasa (tanpa UUID key)
                            if (is_array($photoArr['path'])) {
                                $photoArr['path'] = array_values($photoArr['path']);
                            }
                        }
                        return [(string) \Illuminate\Support\Str::uuid() => $photoArr];
                    })->toArray();
                    $data['repair_items'] = \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mapRepairItemsForForm($device->repairItems);
                }
            }
            return $data;
        };

        return $schema
            ->columns(5)
            ->components([
                Section::make('delivered_at')
                    ->heading(function (Get $get) {
                        return new HtmlString(
                            '<div class="text-center w-full font-semibold text-lg bg-red-500 rounded-xl p-3">
                                <span>Informasi Serah Terima</span>
                            </div>'
                        );
                    })
                    ->columnSpanFull()
                    ->collapsible()
                    ->visible(function (?Model $record) {
                        $repair = $record instanceof \App\Models\Tenant\Repair ? $record : $record?->repair;
                        return $repair?->status == RepairStatus::Delivered;
                    })
                    // ->collapsed(false)
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('delivered_at')
                            ->label('Tanggal Serah Terima'),
                        TextInput::make('recipient_name')
                            ->label('Nama Penerima')
                            ->formatStateUsing(function (?Model $record) {
                                $repair = $record instanceof \App\Models\Tenant\Repair ? $record : $record?->repair;
                                return $repair?->repairHandover()->where('type', 'pickup')->first()?->recipient_name;
                            }),
                        FileUpload::make('invoice_photo')
                            ->image()
                            ->maxSize(5120)
                            ->openable()
                            ->downloadable()
                            // ->columnSpanFull()
                            ->label('Foto Bukti Serah Terima / Pengambilan')
                            ->formatStateUsing(function (?Model $record) {
                                $repair = $record instanceof \App\Models\Tenant\Repair ? $record : $record?->repair;
                                return $repair?->repairHandover()->where('type', 'pickup')->first()?->photo_path;
                            })
                            // ->multiple([
                            //     'max' => 3,
                            // ])
                            // ->collapsed()
                            ->directory(function (?Model $record) {
                                $code = $record instanceof \App\Models\Tenant\Repair ? $record->code : $record?->repair?->code;
                                return "images/repair_photos/{$code}";
                            })
                            // ->getUploadedFileNameForStorageUsing(fn($file) => now()->timestamp . '.' . $file->getClientOriginalExtension())
                            ,

                            
                        \Filament\Forms\Components\Placeholder::make('signature_preview')
                            ->label('Tanda Tangan')
                            ->content(function (?Model $record) {
                                $repair = $record instanceof \App\Models\Tenant\Repair ? $record : $record?->repair;
                                $base64 = $repair?->repairHandover()->where('type', 'pickup')->first()?->signature;
                                
                                if ($base64) {
                                    return new \Illuminate\Support\HtmlString('<img src="' . $base64 . '" alt="Signature" class="max-w-xs rounded-lg border border-gray-300 dark:border-gray-700"/>');
                                }
                                
                                return 'Belum ada tanda tangan';
                            }),
                    ]),

                Grid::make(3)
                    // ->columnSpanFull()
                    ->columnSpan(fn(Get $get) => $get('add_items') ? 3 : 5)
                    ->schema([
                        



                        // DEBUGGING
                        // Select::make('status')
                        //     ->options(RepairStatus::class)
                        //     ->default(RepairStatus::cases()[0]->value)
                        //     // ->visible(fn() => false)
                        //     ->dehydrated(true),

                        TextInput::make('code')
                            ->label('Repair Code')
                            ->default(function (Get $get) {
                                $branchId = session('current_branch_id') ?? $get('branch_id');

                                if (empty($branchId)) {
                                    return null;
                                }

                                $branch = Branch::find($branchId);

                                if (! $branch) {
                                    return null;
                                }

                                return app(RepairService::class)->generateRepairCode($branch)['code'];
                            })
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('branch_id')
                            ->live()
                            ->preload()
                            ->required()
                            ->searchable()
                            ->label('Branch')
                            ->placeholder('Pilih')
                            ->relationship('branch', 'name')
                            ->visible(fn() => empty(session('current_branch_id')))
                            // ->default(fn () => session('current_branch_id'))
                            ->afterStateUpdated(function (?int $state, Set $set, $component ) {
                                // dd($component->getContainer()->getRawState());

                                if (blank($state)) {
                                    $set('code', null);

                                    return;
                                }

                                $branch = Branch::find($state);

                                if (! $branch) {
                                    $set('code', null);

                                    return;
                                };

                                $set(
                                    'code',
                                    app(RepairService::class)->generateRepairCode($branch)['code']
                                );
                            })
                            ->suffixAction(
                                Action::make('add_branch')
                                    ->icon('heroicon-o-plus')
                                    ->url(BranchResource::getUrl('create'))
                                    ->openUrlInNewTab()
                                    ->tooltip('Add Branch')
                            ),

                        Hidden::make('branch_id')
                            ->default(fn() => session('current_branch_id'))
                            ->visible(fn() => filled(session('current_branch_id')))
                            ->dehydrated(true),

                        Select::make('client_id')
                            ->label('Pelanggan')
                            ->live()
                            ->preload()
                            ->optionsLimit(10)
                            ->required()
                            ->searchable(['name', 'phone'])
                            ->placeholder('Pilih Pelanggan')
                            ->relationship('client', 'name')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} - 0{$record->phone}")
                            ->createOptionForm(\App\Filament\Tenant\Resources\Clients\Schemas\ClientForm::getComponents()),
                        DateTimePicker::make('delivered_at')
                            ->default(now()),
                        Select::make('received_by')
                            ->live()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->default(function (Get $get) {
                                $inBranch = Auth::user()->isInBranch($get('branch_id'));
                                // dd($inBranch);
                                if ($inBranch) {
                                    return Auth::user()->id;
                                }

                                return null;
                            })
                            ->visible(fn(Get $get) => filled($get('branch_id')))
                            ->relationship(
                                'receivedBy',
                                'name',
                                modifyQueryUsing: function (Builder $query, Get $get) {
                                    $branchId = $get('branch_id');

                                    // menghentikan query saat branch id tidak ada
                                    if (! $branchId) {
                                        $query->whereRaw('1 = 0');

                                        return;
                                    }

                                    // membatasi query berdasarkan branch id
                                    $query->inBranch($branchId);
                                },
                            ),
                        Textarea::make('notes')
                            ->columnSpan(fn(Get $get) => empty(session('current_branch_id')) && filled($get('branch_id'))  ? 1 : 2),

                    ]),

                Section::make('Invoice Details')
                    ->visible(fn($get) => $get('add_items'))
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('invoice_number')
                            ->default(function (Get $get) {
                                $branchId = session('current_branch_id') ?? $get('branch_id');
                                if (empty($branchId)) return null;
                                $branch = Branch::find($branchId);
                                if (! $branch) return null;
                                return app(RepairService::class)->generateInvoiceCode($branch);
                            })
                            ->readOnly()
                            ->dehydrated(false)
                            ->disabled(fn($record) => $record && $record->repairInvoice && $record->repairInvoice->paid_amount > 0)
                            ->placeholder('Auto Generated'),
                        DateTimePicker::make('invoice_date')
                            ->default(now())
                            ->dehydrated(false)
                            ->disabled(fn($record) => $record && $record->repairInvoice && $record->repairInvoice->paid_amount > 0),

                        \Filament\Forms\Components\Placeholder::make('subtotal_display')
                            ->label('Sub Total Keseluruhan')
                            ->content(function (Get $get) {
                                $total = 0;
                                $devices = $get('repair_devices') ?? [];

                                foreach ($devices as $device) {
                                    $items = $device['repair_items'] ?? [];
                                    foreach ($items as $item) {
                                        $total += (float) ($item['subtotal'] ?? (($item['price'] ?? 0) * ($item['qty'] ?? 1)));
                                    }
                                }

                                return new \Illuminate\Support\HtmlString('
                                    <div class="text-base font-bold text-primary-600 dark:text-primary-400 text-lg">
                                        Rp ' . number_format($total, 0, ',', '.') . '
                                    </div>
                                ');
                            }),
                    ]),
                Toggle::make('multiple_devices')
                    ->live()
                    ->default(false),

                Toggle::make('add_items')
                    ->live()
                    ->dehydrated(false),

                Repeater::make('repair_devices')
                    ->live()
                    ->defaultItems(1)
                    ->addable(false)
                    ->deletable(false)
                    ->columnSpanFull()
                    // ->columns(1)
                    ->relationship('repairDevices')
                    ->mutateRelationshipDataBeforeFillUsing($mutateRepairDeviceData)
                    ->dehydrated(fn(Get $get) => !$get('multiple_devices'))
                    ->visible(fn(Get $get) => !$get('multiple_devices'))
                    ->schema(function (Get $get) {
                        return RepairFormDevice::deviceForm($get);
                    }),

                ModalRepeater::make('repair_devices')
                    ->live()
                    ->cloneable()
                    ->modalColumns(1)
                    ->columnSpanFull()
                    ->modalWidth('7xl')
                    ->relationship('repairDevices')
                    ->mutateRelationshipDataBeforeFillUsing($mutateRepairDeviceData)
                    ->emptyLabel('No items added yet.')
                    ->dehydrated(fn(Get $get) => $get('multiple_devices'))
                    ->visible(fn(Get $get) => $get('multiple_devices'))
                    ->tableColumns([
                        Column::make('deviceType.name')->label('Name'),
                        Column::make('brand.name')->label('Brand'),
                        Column::make('model'),
                        Column::make('color'),
                        Column::make('status')->label('Status'),
                        Column::make('problems'),
                        Column::make('accessories')
                    ])
                    ->schema(function (Get $get) {
                        return RepairFormDevice::deviceForm($get);
                    }),
            ]);
    }
}
