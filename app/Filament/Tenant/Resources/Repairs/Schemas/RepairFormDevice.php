<?php

namespace App\Filament\Tenant\Resources\Repairs\Schemas;

use App\Enums\Color;
use App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairTechnician;
use App\Services\DatabaseService;
use Filament\Actions\Action;
// use Filament\Forms\Components\Actions\Action as FormsAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use YousefAman\ModalRepeater\Column;
use YousefAman\ModalRepeater\ModalRepeater;
use Filament\Tables\Columns\ImageColumn;

use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RepairFormDevice
{
    public static function getNestedFieldValue(?Get $get, string $field, ?Get $parentGet = null)
    {
        $val = null;

        if ($get) {
            $val = $get($field)
                ?? $get("../{$field}")
                ?? $get("../../{$field}")
                ?? $get("../../../{$field}");
        }

        if (($val === null || $val === 'null') && $parentGet) {
            $val = $parentGet($field)
                ?? $parentGet("../{$field}")
                ?? $parentGet("../../{$field}")
                ?? $parentGet("../../../{$field}");
        }

        return $val;
    }

    public static function getActiveTechniciansOptions(Get $get, ?Get $parentGet = null): array
    {
        $branchId = session('current_branch_id') ?? self::getNestedFieldValue($get, 'branch_id', $parentGet);

        return \App\Models\Tenant\EmployeeAssignment::with(['employee.user', 'employee.skills.skillSpecialization', 'branch'])
            ->whereHas('employee', fn($query) => $query->where('is_active', true))
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->where(fn($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->get()
            ->mapWithKeys(function ($a) {
                $name = $a->employee?->name ?? 'Unknown';
                $branchName = $a->branch ? ' - ' . $a->branch->name : '';
                $skills = '';

                if ($a->employee && $a->employee->skills->isNotEmpty()) {
                    $skillNames = $a->employee->skills
                        ->map(fn($skill) => $skill->skillSpecialization?->name)
                        ->filter()
                        ->join(', ');

                    if (!empty($skillNames)) {
                        $skills = " [Skill: {$skillNames}]";
                    }
                }

                return [$a->id => $name . $branchName . $skills];
            })
            ->toArray();
    }

    public static function deviceForm(?Get $parentGet = null): array
    {
        $checkAddItems = function (Get $get, $livewire) use ($parentGet): bool {
            $val = self::getNestedFieldValue($get, 'add_items', $parentGet);

            if ($val !== null) {
                return (bool) $val;
            }

            if (isset($livewire->data['add_items'])) {
                return (bool) $livewire->data['add_items'];
            }

            return true;
        };

        return [
            Grid::make(12)
                // ->columnSpan(1)
                ->schema([
                    Grid::make(12)
                        ->columnSpan(fn(Get $get, $livewire) => $checkAddItems($get, $livewire) ? 5 : 12)
                        ->schema([
                            Grid::make(3)
                                ->live()
                                ->columnSpan(fn(Get $get, $livewire) => $checkAddItems($get, $livewire) ? 12 : 6)
                                ->schema([

                                    Text::make(function (Get $get) {
                                        $statusValue = $get('status') ?? \App\Enums\RepairDeviceStatus::Todo->value;
                                        return \App\Enums\RepairDeviceStatus::tryFrom($statusValue)?->getLabel() ?? 'Unknown';
                                    })
                                        ->badge()
                                        ->columnSpanFull()
                                        ->size(TextSize::Large)
                                        ->color(function (Get $get) {
                                            $statusValue = $get('status') ?? \App\Enums\RepairDeviceStatus::Todo->value;
                                            return \App\Enums\RepairDeviceStatus::tryFrom($statusValue)?->getColor() ?? 'gray';
                                        })
                                        ->icon(function (Get $get) {
                                            $statusValue = $get('status') ?? \App\Enums\RepairDeviceStatus::Todo->value;
                                            return \App\Enums\RepairDeviceStatus::tryFrom($statusValue)?->getIcon();
                                        }),
                                    Hidden::make('status') 
                                        ->default(\App\Enums\RepairDeviceStatus::Todo->value),


                                    // Select::make('status')
                                    //     ->label('Status Pengerjaan')
                                    //     ->columnSpanFull()
                                    //     ->options(\App\Enums\RepairDeviceStatus::class)
                                    //     ->default(\App\Enums\RepairDeviceStatus::Todo->value)
                                    //     ->disabled()
                                    //     ->dehydrated()
                                    //     ,
                                    Select::make('device_type_id')
                                        ->relationship('deviceType', 'name')
                                        ->live()
                                        ->required()
                                        ->default(1)
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $set(
                                                'checklist',
                                                ChecklistTemplate::query()
                                                    ->where('device_type_id', $state)
                                                    ->where('is_active', true)
                                                    ->get()
                                                    ->map(fn($template) => [
                                                        'checklist_template_id' => $template->id,
                                                        'name' => $template->name,
                                                        'status' => null,
                                                        'note' => null,
                                                    ])
                                                    ->toArray()
                                            );
                                        }),
                                    Select::make('brand_id')
                                        ->placeholder('pilih')
                                        ->columnSpan(2)
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->relationship(
                                            'brand',
                                            'name',
                                            modifyQueryUsing: fn($query) => $query->forDevice(),
                                        )
                                        ->createOptionForm([
                                            Grid::make()
                                                ->columns(3)
                                                ->schema([

                                                    TextInput::make('name')
                                                        ->required()
                                                        ->label('Brand Name'),
                                                    Select::make('type')
                                                        ->default('device')
                                                        ->disabled()
                                                        ->dehydrated(true)
                                                        ->options(
                                                            app(DatabaseService::class)->getEnumValues('brands', 'type')
                                                        ),
                                                    Textarea::make('description'),
                                                ]),
                                        ]),
                                    TextInput::make('color')
                                        ->label('Warna')
                                        ->required()
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
                                        ->columnSpan(2)
                                        ->placeholder('pilih')
                                        ->preload()
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
                                        })
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Model Name')
                                                ->required(),
                                        ])
                                        ->createOptionUsing(function (array $data, Get $get) {
                                            $brandId = $get('brand_id');
                                            $deviceTypeId = $get('device_type_id');

                                            if (! $brandId || ! $deviceTypeId) {
                                                Notification::make()
                                                    ->title('Gagal Membuat Model Baru')
                                                    ->body('Pilih Tipe Perangkat dan Brand terlebih dahulu di form utama sebelum membuat Model baru.')
                                                    ->danger()
                                                    ->send();

                                                throw \Illuminate\Validation\ValidationException::withMessages([
                                                    'name' => 'Pilih Tipe Perangkat dan Brand terlebih dahulu di form utama sebelum membuat Model baru.',
                                                ]);
                                            }

                                            $model = \App\Models\Tenant\DeviceModel::create([
                                                'name' => $data['name'],
                                                'brand_id' => $brandId,
                                                'device_type_id' => $deviceTypeId,
                                            ]);

                                            // Return the string name so it is saved in the model field
                                            return $model->name;
                                        }),
                                    TextInput::make('serial_number')
                                        ->numeric(),
                                    TextInput::make('imei')
                                        ->numeric(),

                                    Toggle::make('is_assign_technician')
                                        ->columnSpan(1)
                                        ->live()
                                        ->label('Assign Technician')
                                        ->default(false)
                                        ->visible(fn(Get $get) => empty($get('id')) || empty($get('technicians')))
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (!$state) {
                                                $set('primary_technician_id', null);
                                                $set('technicians', []);
                                            }
                                        }),

                                    Select::make('primary_technician_id')
                                        ->columnSpan(2)
                                        ->live()
                                        ->label('Pilih Teknisi Utama')
                                        ->visible(fn(Get $get) => (empty($get('id')) || empty($get('technicians'))) && $get('is_assign_technician'))
                                        ->options(function (Get $get) use ($parentGet) {
                                            return self::getActiveTechniciansOptions($get, $parentGet);
                                        })
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state) {
                                                $set('technicians', [
                                                    (string) \Illuminate\Support\Str::uuid() => [
                                                        'employee_assignment_id' => $state,
                                                        'role' => 'primary',
                                                    ]
                                                ]);
                                            } else {
                                                $set('technicians', []);
                                            }
                                        })
                                        ->dehydrated(false),


                                    Actions::make([
                                        \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::getDokumenGambarAction($parentGet),
                                    ])
                                        ->columnSpan(2),
                                    Hidden::make('repair_photos')->default([]),
                                ]),
                            \App\Filament\Tenant\Resources\Repairs\Helpers\RepairFormLogic::getChecklistRepeater()
                                ->columnSpan(fn(Get $get, $livewire) => $checkAddItems($get, $livewire) ? 12 : 6)
                        ]),

                    Grid::make(7)
                        ->columnSpan(7)
                        ->schema([

                            RepairFormItem::schema($parentGet, $checkAddItems)
                                ->columnSpanFull(),
                            RepairFormLogic::getTechniciansRepeater($parentGet, false)
                                ->columnSpan(fn(Get $get, $livewire) => $checkAddItems($get, $livewire) ? 7 : 6)
                        ])
                ]),
            Grid::make()
                ->columns(4)
                ->columnSpanFull()
                ->schema([
                    Textarea::make('problems'),
                    Textarea::make('diagnosis'),
                    Textarea::make('accessories')
                        ->label('Kelengkapan bawaan'),
                    Textarea::make('notes'),
                ]),
        ];
    }

}
