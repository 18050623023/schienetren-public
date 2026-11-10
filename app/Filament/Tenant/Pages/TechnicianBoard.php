<?php

namespace App\Filament\Tenant\Pages;

use App\Enums\RepairDeviceStatus;
use App\Enums\TenantFeature;
use App\Models\Tenant\RepairDevice;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Filament\Tenant\Resources\Repairs\Schemas\RepairFormDevice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardPage;
use Relaticle\Flowforge\Column;
use UnitEnum;
use Filament\Forms\Components\Hidden;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ToggleButtons;
use YousefAman\ModalRepeater\Column as ModalColumn;
use YousefAman\ModalRepeater\ModalRepeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Tenant\ChecklistTemplate;
use Illuminate\Database\Eloquent\Model;
use App\Services\DatabaseService;
use App\Enums\Color;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\Tenant\Resources\Repairs\Schemas\RepairFormItem;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Notifications\Notification;
use Relaticle\Flowforge\Components\CardFlex;
use App\Filament\Tenant\Resources\Repairs\Pages\Concerns\HandlesRepairItemSelection;

class TechnicianBoard extends BoardPage
{
    use HandlesRepairItemSelection;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Technician Board';
    protected static ?string $title = 'Technician Kanban Board';
    protected static string | UnitEnum | null $navigationGroup = 'Work In Progress';

    protected string $view = 'filament.tenant.pages.technician-board';

    public string $activeTab = 'board';

    protected function getBoardView(): string
    {
        return 'filament.tenant.pages.technician-board';
    }


    // canaccess
    public static function canAccess(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::KANBAN_TECHNICIAN)) {
            return false;
        }

        return Auth::user()?->hasRole(array_merge(
            (array) settingTenant('tenant_technical_roles', ['technician']),
            (array) settingTenant('tenant_bypass_roles', ['owner'])
        ));
    }

    public function moveCard(
        string $cardId,
        string $targetColumnId,
        ?string $afterCardId = null,
        ?string $beforeCardId = null
    ): void {
        $card = RepairDevice::find($cardId);

        // Cegah perpindahan keluar dari status QC jika melalui drag-and-drop
        if ($card && $card->status === RepairDeviceStatus::QC && $targetColumnId !== RepairDeviceStatus::QC->value) {
            Notification::make()
                ->title('Status QC Terkunci')
                ->body('Status QC tidak dapat digeser manual. Silakan klik kartu untuk mengisi formulir hasil QC.')
                ->warning()
                ->send();

            return;
        }

        

        parent::moveCard($cardId, $targetColumnId, $afterCardId, $beforeCardId);
    }

    protected function isReadOnlyStatus(?RepairDeviceStatus $status): bool
    {
        return in_array($status?->value, [
            RepairDeviceStatus::Todo->value,
            RepairDeviceStatus::Escalated->value,
            RepairDeviceStatus::ReadyPickup->value,
            RepairDeviceStatus::Done->value,
            RepairDeviceStatus::NotPossible->value,
            RepairDeviceStatus::Cancel->value,
        ]);
    }

    protected function mutateRecordDataForForm(array $data, Model $record): array
    {
        $data['repair_photos'] = $record->repairPhotos->mapWithKeys(function ($photo) {
            $photoArr = $photo->toArray();
            if (isset($photoArr['path'])) {
                if (is_string($photoArr['path'])) {
                    $decoded = json_decode($photoArr['path'], true);
                    $photoArr['path'] = is_array($decoded) ? $decoded : [$photoArr['path']];
                }
                if (is_array($photoArr['path'])) {
                    $photoArr['path'] = array_values($photoArr['path']);
                }
            }
            return [(string) \Illuminate\Support\Str::uuid() => $photoArr];
        })->toArray();

        $data['repair_items'] = \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::mapRepairItemsForForm($record->repairItems);
        $data['technicians'] = $record->technicians->mapWithKeys(fn($tech) => [(string) \Illuminate\Support\Str::uuid() => $tech->toArray()])->toArray();
        return $data;
    }



    public function board(Board $board): Board
    {
        return $board
            ->searchable(['model', 'brand'])
            ->filters([
                SelectFilter::make('brand')->relationship('brand', 'name'),
            ])
            ->query($this->getEloquentQuery())
            ->recordTitleAttribute('brand_model')
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->cardsPerColumn(10)
            // Uncomment if you add a 'position' column to your database for ordering within columns
            // ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            // ->filters([
            //     SelectFilter::make('brand')
            //         ->relationship('brand', 'name'),
            //     Filter::make('waiting_confirmation')->query(fn($q) =>
            //         $q->where('status', RepairDeviceStatus::WaitingConfirmation->value)
            //     ),
            //     Filter::make('not_possible')->query(fn($q) =>
            //         $q->where('status', RepairDeviceStatus::NotPossible->value)
            //     ),
            //     Filter::make('cancel')->query(fn($q) =>
            //         $q->where('status', RepairDeviceStatus::Cancel->value)
            //     ),
            // ])
            ->cardSchema(
                fn(Schema $schema) => $schema->components([
                    TextEntry::make('brand_model')
                        // ->label('Diterima Oleh')
                        ->hiddenLabel()
                        // ->color('gray')
                        ->size('m')
                        ->weight(FontWeight::SemiBold)
                        ->formatStateUsing(function ($state, $record) {
                            $deviceType = $record->deviceType->name;
                            return $deviceType. ' - ' . $state;
                        }),
                    // TextEntry::make('repair.created_at')
                    //     // ->label('Repair Masuk')
                    //     ->hiddenLabel()
                    //     ->icon('heroicon-o-calendar')
                    //     ->color('gray')
                    //     ->size('sm')
                    //     ->formatStateUsing(function ($state, $record) {
                    //         // dd($record->repair->createdBy()->get());
                    //         // $createdBy = $record->repair->createdBy()->first();
                    //         return Carbon::parse($state)->locale('id')->translatedFormat('d F Y H:i');
                    //     }),
                    CardFlex::make([
                        TextEntry::make('repair.client.name')->label('Client'),
                        TextEntry::make('repair.code')->label('Repair Code'),
                        // TextEntry::make('due_date')->icon('heroicon-o-calendar'),
                        // ImageEntry::make('assignee.avatar_url')->circular()->size(24),
                    ])
                        ->justify('between')  // Horizontal: 'start', 'end', 'between', 'center'
                        ->align('center'),    // Vertical: 'start', 'end', 'center'.

                    CardFlex::make([
                        TextEntry::make('repair.created_at')
                            ->hiddenLabel()
                            ->badge()
                            ->icon('heroicon-o-calendar')
                            ->formatStateUsing(function ($state, $record) {
                                // dd($record->repair->createdBy()->get());
                                // $createdBy = $record->repair->createdBy()->first();
                                return Carbon::parse($state)->locale('id')->translatedFormat('d F Y');
                            }),

                        TextEntry::make('repair.created_at')
                            ->hiddenLabel()
                            ->badge()
                            ->icon('heroicon-o-clock')
                            ->formatStateUsing(function ($state) {
                                return Carbon::parse($state)->locale('id')->translatedFormat('H:i');
                            }),
                    ]),


                    CardFlex::make([
                        TextEntry::make('repair.createdBy.name')
                            ->hiddenLabel()
                            ->badge()
                            ->icon(Heroicon::OutlinedUser)

                            // ->default('Sistem / Tidak Diketahui') // <--- Tambahkan ini agar tetap muncul saat null
                            // ATAU opsi ke-2 menggunakan state() jika Anda ingin kendali penuh:

                            ->state(function (?Model $record) {
                                // dd($record->repair?->createdBy);
                                return $record->repair?->createdBy?->name ?? 'Sistem / Tidak Diketahui';
                            }),
                    ]),
                ])
            )
            ->columns(
                collect(RepairDeviceStatus::cases())
                    ->reject(fn(RepairDeviceStatus $status) => in_array($status, [
                        RepairDeviceStatus::Done,
                        RepairDeviceStatus::NotPossible,
                        RepairDeviceStatus::Cancel,
                    ]))
                    ->sortBy(fn(RepairDeviceStatus $status, int $key) => match ($status) {
                        RepairDeviceStatus::Escalated => -1, 
                        RepairDeviceStatus::Todo => -2,
                        default => $key,
                    })
                    ->values()
                    ->map(
                        fn(RepairDeviceStatus $status) =>
                        Column::make($status->value)
                            ->label($status->getLabel())
                            ->color($status->getColor())
                            ->icon($status->getIcon())
                    )->toArray()
            )
            ->recordActions([
                Action::make('handoverToSoftware')
                    ->label('Selesai Hardware - Lempar ke Software')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Lempar ke Bagian Software')
                    ->modalDescription('Tandai bagian hardware sudah selesai dan kembalikan ke Todo untuk teknisi software?')
                    ->visible(fn (?Model $record) => $record && $record->status === \App\Enums\RepairDeviceStatus::InProgress && $record->current_stage !== 'software')
                    ->action(function (Model $record) {
                        $record->update([
                            'current_stage' => 'software',
                            'status' => \App\Enums\RepairDeviceStatus::Todo
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil dilempar ke antrean Software')
                            ->success()
                            ->send();
                    }),

                Action::make('clickCard')
                    ->extraAttributes(['style' => 'display: none !important;'])
                    ->action(function (Model $record, $livewire) {
                        if ($this->isReadOnlyStatus($record->status)) {
                            $livewire->replaceMountedAction('view', [], ['recordKey' => $record->getKey()]);
                        } else {
                            $livewire->replaceMountedAction('edit', [], ['recordKey' => $record->getKey()]);
                        }
                    }),

                ViewAction::make()
                    ->visible(fn(?Model $record) => $record === null || $this->isReadOnlyStatus($record->status))
                    ->schema($this->getEditFormSchema())
                    ->modalWidth('7xl')
                    ->modalHeading('View Repair Device Data')
                    ->mutateRecordDataUsing(fn(array $data, Model $record): array => $this->mutateRecordDataForForm($data, $record)),

                EditAction::make()
                    ->hidden(fn(?Model $record) => $record !== null && $this->isReadOnlyStatus($record->status))
                    ->schema($this->getEditFormSchema())
                    ->modalWidth('7xl')
                    ->modalHeading('Edit Repair Device Data')
                    ->mutateRecordDataUsing(fn(array $data, Model $record): array => $this->mutateRecordDataForForm($data, $record))
                    ->using(function (Model $record, array $data): Model {
                        // Handle QC Status
                        $qcStatus = $data['qc_status'] ?? null;
                        $qcNotes = $data['qc_notes'] ?? null;
                        unset($data['qc_status'], $data['qc_notes']);
                        
                        $record->update($data);

                        // Sync repair_photos
                        $photos = $data['repair_photos'] ?? [];
                        $existingPhotoIds = collect($photos)->pluck('id')->filter()->toArray();
                        $record->repairPhotos()->whereNotIn('id', $existingPhotoIds)->delete();

                        foreach ($photos as $photo) {
                            $photoData = \Illuminate\Support\Arr::except($photo, ['created_at', 'updated_at']);

                            if (isset($photoData['path']) && is_array($photoData['path'])) {
                                $photoData['path'] = array_values($photoData['path']);
                            }

                            if (!empty($photo['id'])) {
                                $record->repairPhotos()->find($photo['id'])?->update($photoData);
                            } else {
                                $record->repairPhotos()->create($photoData);
                            }
                        }

                        // Sync repair_items
                        $items = $data['repair_items'] ?? [];
                        $repair = $record->repair;
                        \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::syncRepairItems(
                            $record, 
                            $items, 
                            $repair?->branch_id ?? session('current_branch_id'), 
                            $repair?->code
                        );

                        // Invoice recalculation is now handled automatically by the RepairItem model events (saved/deleted).


                        // Sync technicians relasi yang berhubungan dengan repair device ke teknisi
                        $technicians = $data['technicians'] ?? [];
                        $existingTechIds = collect($technicians)->pluck('id')->filter()->toArray();
                        $record->technicians()->whereNotIn('id', $existingTechIds)->delete();

                        foreach ($technicians as $tech) {
                            $techData = \Illuminate\Support\Arr::except($tech, ['created_at', 'updated_at']);
                            if (!empty($tech['id'])) {
                                $record->technicians()->find($tech['id'])?->update($techData);
                            } else {
                                $record->technicians()->create($techData);
                            }
                        }

                        if ($record->status === \App\Enums\RepairDeviceStatus::QC) {
                            if ($qcStatus === 'passed') {
                                $record->update(['status' => \App\Enums\RepairDeviceStatus::ReadyPickup]);
                            } elseif ($qcStatus === 'failed') {
                                $record->update([
                                    'status' => \App\Enums\RepairDeviceStatus::InProgress,
                                    'notes' => trim(($record->notes ?? '') . "\n\n[QC Failed]: " . $qcNotes)
                                ]);
                            }
                        }

                        return $record;
                    })
            ])
            ->cardAction('clickCard');
    }

    public function getEloquentQuery(): Builder
    {
        $query = RepairDevice::query()
            ->whereNotIn('status', [
                RepairDeviceStatus::Done->value,
                RepairDeviceStatus::NotPossible->value,
                RepairDeviceStatus::Cancel->value,
            ]);
        
        $user = Auth::user();
        if (!$user || $user->hasRole(array_merge((array) settingTenant('tenant_bypass_roles', ['owner'])))) {
            return $query;
        }

        $hasSoftwareSkill = false;
        $userAssignmentIds = [];

        if ($user->employees) {
            $userAssignmentIds = $user->employees->assignments->pluck('id')->toArray();

            if ($user->employees->skills) {
                foreach ($user->employees->skills as $skill) {
                    $name = strtolower($skill->skillSpecialization?->name ?? '');
                    if (str_contains($name, 'software') || str_contains($name, 'os') || str_contains($name, 'program')) {
                        $hasSoftwareSkill = true;
                        break;
                    }
                }
            }
        }

        return $query->where(function ($q) use ($hasSoftwareSkill, $userAssignmentIds) {
            // KONDISI A: Antrean Bebas (Todo & Escalated)
            $q->where(function ($qPool) use ($hasSoftwareSkill) {
                $qPool->whereIn('status', [
                    \App\Enums\RepairDeviceStatus::Todo->value,
                    \App\Enums\RepairDeviceStatus::Escalated->value
                ]);
                
                if ($hasSoftwareSkill) {
                    $qPool->where('current_stage', 'software');
                } else {
                    $qPool->where(function($qStage) {
                        $qStage->whereNull('current_stage')
                               ->orWhere('current_stage', '!=', 'software');
                    });
                }
            })
            // KONDISI B: Meja Kerja Pribadi (Checking, InProgress, dll)
            ->orWhere(function ($qPrivate) use ($userAssignmentIds, $hasSoftwareSkill) {
                $qPrivate->whereNotIn('status', [
                    \App\Enums\RepairDeviceStatus::Todo->value,
                    \App\Enums\RepairDeviceStatus::Escalated->value
                ])
                ->whereHas('technicians', function ($qTech) use ($userAssignmentIds) {
                    $qTech->whereIn('employee_assignment_id', $userAssignmentIds);
                });

                // STRICT MODE: Sembunyikan tiket tahap software dari meja teknisi hardware
                if (!$hasSoftwareSkill) {
                    $qPrivate->where(function($qStage) {
                        $qStage->whereNull('current_stage')
                               ->orWhere('current_stage', '!=', 'software');
                    });
                }
            });
        });
    }

    protected function getEditFormSchema(): array
    {
        $checkAddItems = fn(Get $get, $livewire) => true;

        return [
            Grid::make(12)
                ->schema([
                    Section::make('Quality Control (QC)')
                        ->columnSpanFull()
                        ->description('Form ini hanya muncul saat perangkat berada di tahap QC.')
                        ->visible(fn(?Model $record) => $record && $record->status === \App\Enums\RepairDeviceStatus::QC)
                        ->schema([
                            \Filament\Forms\Components\Radio::make('qc_status')
                                ->label('Hasil Quality Control')
                                ->inline()
                                ->options([
                                    'passed' => 'Lolos QC (Lanjut ke Ready to Pickup)',
                                    'failed' => 'Gagal QC (Kembalikan ke In Progress)',
                                ])
                                ->required()
                                ->live(),
                            \Filament\Forms\Components\Textarea::make('qc_notes')
                                ->label('Catatan QC')
                                ->placeholder('Tuliskan detail pengecekan atau alasan jika gagal...')
                                ->required(fn (Get $get) => $get('qc_status') === 'failed')
                        ]),

                    Grid::make(1)
                        ->columnSpan(7)
                        ->schema([
                            Hidden::make('device_type_id'),
                            Hidden::make('repair_photos')->default([]),

                            Section::make('Informasi Perangkat')
                                ->columnSpan(7)
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('customer_name')
                                                ->label('Nama Pelanggan')
                                                ->state(function (?Model $record) {
                                                    if (!$record) return '-';
                                                    if ($record instanceof \App\Models\Tenant\RepairDevice) {
                                                        return $record->repair?->client?->name ?? '-';
                                                    } elseif ($record instanceof \App\Models\Tenant\Repair) {
                                                        return $record->client?->name ?? '-';
                                                    }
                                                    return '-';
                                                })
                                                ->action(\App\Filament\Tenant\Resources\Clients\Actions\ClientHistoryAction::make(Action::class, 'client_history_modal')),
                                            TextEntry::make('repair_code')
                                                ->label('No. Reparasi')
                                                ->state(function (?Model $record) {
                                                    if (!$record) return '-';
                                                    if ($record instanceof \App\Models\Tenant\RepairDevice) {
                                                        return $record->repair?->code ?? '-';
                                                    } elseif ($record instanceof \App\Models\Tenant\Repair) {
                                                        return $record->code ?? '-';
                                                    }
                                                    return '-';
                                                }),
                                        ]),
                                    Grid::make(3)
                                        ->schema([
                                            TextEntry::make('brand_model_color')
                                                ->label('Brand/Model/Warna')
                                                ->state(function (?Model $record) {
                                                    if (!$record) return '-';
                                                    $brand = $record->brand?->name ?? '-';
                                                    $model = $record->model ?? '-';
                                                    $color = $record->color ?? '-';
                                                    return "{$brand} {$model} {$color}";
                                                }),
                                        ]),
                                    // Select::make('status')
                                    //     ->inlineLabel()
                                    //     // ->label('Status Pengerjaan')
                                    //     ->options(\App\Enums\RepairDeviceStatus::class)
                                    //     ->required(),
                                    Grid::make(2)
                                        ->schema([
                                            Actions::make([

                                                Action::make('info_tambahan')
                                                    ->label('Info Perangkat & Kelengkapan')
                                                    ->icon('heroicon-o-information-circle')
                                                    // ->color('info')
                                                    ->modalHeading('Detail Perangkat')
                                                    ->modalDescription('Informasi IMEI/SN dan kelengkapan bawaan perangkat.')
                                                    ->modalSubmitAction(false) // Sembunyikan tombol submit karena hanya untuk melihat data
                                                    ->modalCancelActionLabel('Tutup')
                                                    ->fillForm(function (Get $get, ?Model $record) {
                                                        $imei = $record?->imei ?? '-';
                                                        $sn = $record?->serial_number ?? '-';
                                                        return [
                                                            'status' => $record?->status,
                                                            'color' => $record?->color,
                                                            'model' => $record?->model,
                                                            'serial_number' => $record?->serial_number,
                                                            'imei' => $record?->imei,
                                                            'imei_sn_modal' => "{$imei} / {$sn}",
                                                            'accessories_modal' => $get('accessories') ?? $record?->accessories,
                                                            'problems' => $get('problems') ?? $record?->problems,
                                                        ];
                                                    })
                                                    ->schema([
                                                        Section::make('Informasi Perangkat')
                                                            ->columns(3)
                                                            ->schema([
                                                                Select::make('device_type_id')
                                                                    ->relationship('deviceType', 'name')
                                                                    ->live()
                                                                    ->disabled(),
                                                                Select::make('brand_id')
                                                                    ->placeholder('pilih')
                                                                    ->searchable()
                                                                    ->preload()
                                                                    ->disabled()
                                                                    ->relationship(
                                                                        'brand',
                                                                        'name',
                                                                        modifyQueryUsing: fn($query) => $query->forDevice(),
                                                                    ),
                                                                Select::make('status')
                                                                    ->label('Status Pengerjaan')
                                                                    ->options(\App\Enums\RepairDeviceStatus::class)
                                                                    ->disabled(),
                                                                TextInput::make('color')
                                                                    ->label('Warna')
                                                                    ->disabled()
                                                                    ->autocomplete('off')
                                                                    ->datalist(
                                                                        collect(Color::cases())
                                                                            ->reject(fn($color) => $color === Color::Custom)
                                                                            ->map(fn($color) => $color->label())
                                                                            ->values()
                                                                            ->toArray()
                                                                    ),
                                                                // TextInput::make('model')
                                                                Select::make('model')
                                                                    ->placeholder('pilih')
                                                                    ->optionsLimit(10)
                                                                    ->required()
                                                                    ->searchable()
                                                                    ->options(function (Get $get) {
                                                                        $brandId = $get('brand_id');
                                                                        $deviceTypeId = $get('device_type_id');

                                                                        if (! $brandId || ! $deviceTypeId) {
                                                                            return [];
                                                                        }

                                                                        // Pluck name as both key and value to save as string
                                                                        return \App\Models\Tenant\DeviceModel::query()
                                                                            ->where('brand_id', $brandId)
                                                                            ->where('device_type_id', $deviceTypeId)
                                                                            ->pluck('name', 'name');
                                                                    }),
                                                                TextInput::make('serial_number')
                                                                    ->numeric(),
                                                                TextInput::make('imei')
                                                                    ->numeric(),
                                                            ]),
                                                        Section::make('Keterangan')
                                                            ->columns(2)
                                                            ->schema([
                                                                Textarea::make('accessories_modal')
                                                                    ->label('Kelengkapan bawaan')
                                                                    ->disabled(),
                                                                Textarea::make('problems')
                                                                    ->label('Keluhan (Dari CS)')
                                                                    ->disabled(),
                                                            ])
                                                    ]),
                                                \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::getDokumenGambarAction(),
                                            ])
                                                ->columns(2)
                                                ->columnSpanFull(),
                                        ]),
                                ]),

                             Section::make('Teknisi')
                                ->columnSpan(7)
                                ->compact()
                                ->collapsed()
                                ->schema([
                                    \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::getTechniciansRepeater(null, true)->hiddenLabel(),

                                ]),
                            
                            RepairFormItem::schema(null, $checkAddItems)
                                ->columnSpan(7),

                        ]),
                    Grid::make(2)
                        ->columnSpan(5)
                        ->schema([
                            Section::make('Rincian Biaya')
                                ->columnSpan(2)
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('total_harga')
                                        ->label('Total Harga')
                                        ->state(function (?Model $record) {
                                            $invoice = $record instanceof \App\Models\Tenant\RepairDevice 
                                                ? $record->repair?->repairInvoice 
                                                : ($record instanceof \App\Models\Tenant\Repair ? $record->repairInvoice : null);
                                            return $invoice?->grand_total ?? 0;
                                        })
                                        ->money('IDR'),
                                    TextEntry::make('dp_terbayar')
                                        ->label('DP / Terbayar')
                                        ->state(function (?Model $record) {
                                            $invoice = $record instanceof \App\Models\Tenant\RepairDevice 
                                                ? $record->repair?->repairInvoice 
                                                : ($record instanceof \App\Models\Tenant\Repair ? $record->repairInvoice : null);
                                            return $invoice?->paid_amount ?? 0;
                                        })
                                        ->money('IDR'),
                                    TextEntry::make('sisa_bayar')
                                        ->label('Sisa Bayar')
                                        ->state(function (?Model $record) {
                                            $invoice = $record instanceof \App\Models\Tenant\RepairDevice 
                                                ? $record->repair?->repairInvoice 
                                                : ($record instanceof \App\Models\Tenant\Repair ? $record->repairInvoice : null);
                                            return $invoice?->remaining_amount ?? 0;
                                        })
                                        ->money('IDR'),
                                ]),
                                        
                            Textarea::make('problems')
                                ->columnSpanFull()
                                ->label('Keluhan (Dari CS)')
                                ->disabled(),
                            Textarea::make('diagnosis'),
                            Textarea::make('notes')->label('Catatan Tambahan (Opsional)'),
                            \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::getChecklistRepeater()->columnSpanFull(),

                        ]),
            
                ]),
           

        ];
    }
}
