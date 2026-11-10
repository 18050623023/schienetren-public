<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Schemas;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Illuminate\Support\Facades\Auth;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Stock Opname')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', modifyQueryUsing: function ($query, $operation) {
                                if ($operation === 'create') {
                                    $query->whereDoesntHave('stockOpnames', fn ($q) => $q->where('type', 'initial'))
                                          ->whereDoesntHave('stocks', fn ($q) => $q->where('stock', '>', 0));
                                }
                            })
                            ->helperText(fn (string $operation) => $operation === 'create' ? 'Hanya cabang baru dengan stok 0 yang dapat dipilih.' : null)
                            ->required(),
                        Forms\Components\Select::make('createdBy.name')
                            ->relationship('createdBy', 'name')
                            ->label('Created By')
                            ->default(Auth::user()->id)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextArea::make('note')
                            ->label('Catatan')
                            ->columnSpan(3),
                    ]),

                Section::make('Items')
                    ->columnSpanFull()
                    ->description('Tambahkan produk dan jumlahnya. Jika produk memiliki serial, field serial akan muncul.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Products')
                            ->required()
                            ->collapsible()
                            // ->createItemButtonLabel('Add product')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::all()->where('type', 'product')->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->reactive()
                                    // ->afterStateUpdated(fn (callable $set) => $set('product_variant_id', null))
                                    ->afterStateUpdated(function ($state, callable $set) {

                                        if (!$state) {
                                            $set('product_variant_id', null);
                                            return;
                                        }

                                        $product = Product::find($state);

                                        if (!$product) {
                                            $set('product_variant_id', null);
                                            return;
                                        }

                                        // Jika product tidak punya variant → set otomatis variant pertama
                                        if (!$product->has_variant) {
                                            $firstVariant = ProductVariant::where('product_id', $product->id)->first();

                                            if ($firstVariant) {
                                                $set('product_variant_id', $firstVariant->id);
                                            } else {
                                                $set('product_variant_id', null);
                                            }
                                        } else {
                                            // Jika punya variant → kosongkan select
                                            $set('product_variant_id', null);
                                        }
                                    })
                                    ->required(),

                                Forms\Components\Select::make('product_variant_id')
                                    ->options(function (callable $get) {
                                        if (!$get('product_id')) return [];
                                        return ProductVariant::where('product_id', $get('product_id'))->pluck('sku', 'id');
                                    })
                                    ->disabled(fn ($get) =>
                                        $get('product_id')
                                        && Product::find($get('product_id'))?->has_variant == 0
                                        && $get('product_variant_id')
                                    )
                                    ->dehydrated(fn ($get) => filled($get('product_variant_id')))
                                    ->searchable()
                                    ->rule(function ($get) {

                                        return function (string $attribute, $value, Closure $fail) use ($get) {

                                            $items = collect($get('../../items') ?? []);

                                            $duplicates = $items
                                                ->pluck('product_variant_id')
                                                ->filter()
                                                ->countBy()
                                                ->filter(fn ($count) => $count > 1);

                                            if ($duplicates->has($value)) {
                                                $fail('Variant ini sudah dipilih di baris lain.');
                                            }
                                        };
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        // auto-populate serial rows if product uses serial
                                        // this will set an array of empty serials based on qty
                                        $productId = $get('product_id');
                                        if (! $productId) {
                                            return;
                                        }

                                        $product = Product::find($productId);
                                        if (! $product) {
                                            return;
                                        }

                                        if ($product->has_sn) {
                                            $serials = [];
                                            for ($i = 0; $i < (int) $state; $i++) {
                                                $serials[] = [ 'serial_number' => '' ];
                                            }
                                            $set('serials', $serials);
                                        }
                                    }),

                                Forms\Components\Textarea::make('note')
                                    ->label('Catatan')
                                    ->rows(2),

                                Actions::make([
                                    Action::make('lihat_serials')
                                        ->label('Lihat Serial Numbers')
                                        ->icon('heroicon-o-list-bullet')
                                        ->button()
                                        ->visible(fn ($livewire, callable $get) => 
                                            $livewire instanceof ViewRecord 
                                            && count($get('serials') ?? []) > 0
                                        )
                                        ->modalContent(fn (callable $get) => view('filament.tenant.components.stock-opname-serial-modal', ['itemId' => $get('item_id')]))
                                        ->modalSubmitAction(false)
                                        ->modalCancelActionLabel('Tutup'),
                                ])->columnSpanFull(),

                                // Repeater untuk serial
                                Forms\Components\Repeater::make('serials')
                                    ->label('Serial Numbers')
                                    ->addable(false)
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->grid(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('serial_number')
                                            ->label('Serial')
                                            ->required()
                                            ->columnSpan(fn($record, $livewire)=>($record?->type == 'regular' && $livewire instanceof ViewRecord) ? 2 : 4)
                                            ->maxLength(255)
                                            ->rule(function ($get) {

                                                return function (string $attribute, $value, Closure $fail) use ($get) {

                                                    $items = collect($get('../../serials') ?? []);

                                                    $duplicates = $items
                                                        ->pluck('serial_number')
                                                        ->filter()
                                                        ->countBy()
                                                        ->filter(fn ($count) => $count > 1);

                                                    if ($duplicates->has($value)) {
                                                        $fail('serial number ini sudah tulis di baris lain.');
                                                    }
                                                };
                                            }),
                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->disabled()
                                            ->visible(function ($record, $livewire) {
                                                // dd($record->type);
                                                if ($record?->type == 'regular' && $livewire instanceof ViewRecord) {
                                                    return true;
                                                }
                                                return false;
                                            })
                                                // fn ($record, $livewire) => /* dd($record) */ $livewire instanceof ViewRecord)
                                            ->options([
                                                'OK' => 'OK',
                                                'LOST' => 'Lost',
                                                'EXTRA' => 'Extra',
                                            ]),

                                        Forms\Components\Textarea::make('note')
                                            ->label('Catatan')
                                            ->rows(2)
                                            ->disabled()
                                            ->visible(function ($record, $livewire) {
                                                // dd($record->type);
                                                if ($record?->type == 'regular' && $livewire instanceof ViewRecord) {
                                                    return true;
                                                }
                                                return false;
                                            }),
                                    ])
                                    ->minItems(0)
                                    ->visible(function (callable $get, $livewire) {
                                        if ($livewire instanceof ViewRecord) return false;
                                        $productId = $get('product_id');
                                        if (! $productId) return false;
                                            $product = Product::find($productId);
                                        return $product?->has_sn ?? false;
                                    }),
                            ])
                            ->columns(2)
                            ->required(),
                    ]),
            ]);
    }
}
