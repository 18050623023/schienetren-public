<?php

namespace App\Filament\Tenant\Resources\Products\Schemas;

use App\Models\Tenant\Category;
use App\Services\DatabaseService;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')

                    ->label('Product Code')
                    ->unique('products', 'code')
                    ->required(),
                Select::make('type')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->options([
                        'product' => 'Product',
                        'service' => 'Service',
                    ]),
                FileUpload::make('image')
                    ->columnSpan(2)
                    ->image()       // ini agar hanya menerima gambar
                    ->label('Upload Gambar')
                    ->directory(function () {
                        return 'images/product';
                    }) // folder penyimpanan
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file, callable $get): string => 'product-'.$get('code').'-'.now()->timestamp.'.'.$file->getClientOriginalExtension(),
                    )
                    // ->visibility('public')
                    , // jika public
                /* ->required() */

                Section::make()
                    // ->columnSpan(3)
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        Select::make('brand_id')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->hidden(fn ($get) => $get('type') == 'service')
                            ->relationship('brand', 'name')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Brand Name'),
                                Select::make('type')
                                    ->options(
                                        app(DatabaseService::class)->getEnumValues('brands', 'type')
                                    ),
                                Textarea::make('description'),
                            ]),

                        Textarea::make('description')
                            ->rows(4),
                    ]),

                Section::make()
                    ->columns(1)
                    ->schema([
                        Select::make('unit_id')
                            ->hidden(fn ($get) => $get('type') == 'service')
                            ->label('Satuan')
                            ->searchable()
                            ->preload()
                            ->relationship('unit', 'name')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Unit Name'),
                                Textarea::make('description'),
                            ]),
                        Select::make('categories')
                            ->label('Categories')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->relationship(
                                'categories',
                                'name',
                                modifyQueryUsing: fn ($query) => $query->forProduct(),
                            )
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Category Name'),

                                Textarea::make('description'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Category::create([
                                    'name' => $data['name'],
                                    'description' => $data['description'] ?? null,
                                    'type' => 'product',
                                ])->getKey();
                            }),

                        Select::make('compatibleModels')
                            ->label('Kompatibel Untuk')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->relationship('compatibleModels', 'name', fn ($query) => $query->with('brand'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->brand ? "{$record->brand->name} - {$record->name}" : $record->name)
                            ->createOptionForm([
                                Grid::make(2)
                                ->schema([
                                    Select::make('brand_id')
                                        ->relationship('brand', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    TextInput::make('name')
                                        ->required()
                                        ->label('Model Name'),
                                    Select::make('device_type_id')
                                        ->relationship('deviceType', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Textarea::make('description'),
                                ])
                            ]),

                        // TextInput::make('garansi')
                        //     ->required()
                        //     ->live()
                        //     ,

                        Group::make([
                            TextInput::make('warranty_value')
                                ->numeric()
                                ->label('Garansi')
                                ->placeholder(30)
                                ->minValue(0),

                            Select::make('warranty_unit')
                                ->label('Satuan Garansi')
                                ->options(app(DatabaseService::class)->getEnumValues('products', 'warranty_unit'))
                                // ->default('day'),
                        ])
                        ->columns(2),

                        Radio::make('has_sn')
                            ->label('Apakah Produk Memiliki Serial Number?')
                            ->required()
                            ->boolean()
                            ->inline()
                            ->reactive()
                            ->hidden(fn ($get) => $get('type') == 'service'),
                    ]),

                Section::make()
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('has_variant')
                            ->label('Produk Memiliki Variant?')
                            ->reactive()
                            ->default(false),
                        Repeater::make('variants')
                            ->label('Variant Product')
                            ->relationship()
                            ->columns(fn (callable $get) => $get('has_variant') === true ? 4 : 3)
                            ->addable(fn (callable $get) => $get('has_variant') === true)
                            ->deletable(fn (callable $get) => $get('has_variant') === true)
                            ->schema([
                                TextInput::make('name')
                                    ->hidden(fn (callable $get) => $get('../../has_variant') === false),
                                TextInput::make('sku')
                                    ->rule(function ($get) {
                                        return function (string $attribute, $value, Closure $fail) use ($get) {


                                            $items = collect($get('../../variants') ?? []);

                                            $duplicates = $items
                                                ->pluck('sku')
                                                ->filter()
                                                ->countBy()
                                                ->filter(fn ($count) => $count > 1);

                                            if ($duplicates->has($value)) {
                                                $fail('SKU ini sudah dipilih di baris lain.');
                                            }
                                        };
                                    })
                                    ->required(),
                                TextInput::make('purchase_price')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('sale_price')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
