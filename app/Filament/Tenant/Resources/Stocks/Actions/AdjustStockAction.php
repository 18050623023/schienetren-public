<?php

namespace App\Filament\Tenant\Resources\Stocks\Actions;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\Stock;
use App\Services\Stock\StockAdjustmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;

class AdjustStockAction
{
    /**
     * Action untuk tombol pada setiap baris/kartu Stock.
     */
    public static function make(string $name = 'adjust_stock'): Action
    {
        return Action::make($name)
            ->label('Adjust Stok')
            ->icon('heroicon-o-adjustments-horizontal')
            ->color('warning')
            ->modalHeading(fn (Stock $record) => "Penyesuaian Stok: {$record->product->name}")
            ->modalDescription(fn (Stock $record) => new HtmlString(
                "<strong>Cabang:</strong> " . ($record->branch->name ?? '-') .
                " &bull; <strong>Varian:</strong> " . ($record->productVariant->name ?? '-') .
                " &bull; <strong>Stok Saat Ini:</strong> " . $record->stock . " " . ($record->product->unit->name ?? 'Pcs')
            ))
            ->modalWidth('lg')
            ->fillForm(fn (Stock $record): array => [
                'direction' => 'out',
                'quantity' => 1,
                'reason' => 'Barang Rusak / Pecah',
                'note' => '',
                'selected_serials' => [],
                'new_serials' => [],
            ])
            ->schema(fn (Stock $record): array => [
                Radio::make('direction')
                    ->label('Tipe Penyesuaian')
                    ->options([
                        'out' => 'Kurang / Stok Keluar (-)',
                        'in'  => 'Tambah / Stok Masuk (+)',
                    ])
                    ->default('out')
                    ->inline()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => 
                        $set('reason', $state === 'out' ? 'Barang Rusak / Pecah' : 'Pembelian Kas Eceran')
                    ),

                TextInput::make('quantity')
                    ->label('Jumlah (Kuantitas)')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->live()
                    ->helperText(function (callable $get) use ($record) {
                        $unit = $record->product->unit->name ?? 'Pcs';
                        if ($get('direction') === 'out') {
                            return "Maksimal pengurangan: {$record->stock} {$unit}";
                        }
                        return "Stok bertambah ke saldo cabang";
                    }),

                Select::make('reason')
                    ->label('Alasan Penyesuaian')
                    ->options(fn (callable $get) => $get('direction') === 'out' ? [
                        'Barang Rusak / Pecah' => 'Barang Rusak / Pecah',
                        'Barang Hilang / Selisih' => 'Barang Hilang / Selisih',
                        'Kadaluarsa / Tidak Layak Jual' => 'Kadaluarsa / Tidak Layak Jual',
                        'Pemakaian Internal Bengkel' => 'Pemakaian Internal Bengkel',
                        'Koreksi Fisik Kurang' => 'Koreksi Fisik Kurang',
                        'Lainnya' => 'Lainnya',
                    ] : [
                        'Pembelian Kas Eceran' => 'Pembelian Kas Eceran',
                        'Koreksi Fisik Lebih' => 'Koreksi Fisik Lebih',
                        'Barang Ditemukan Kembali' => 'Barang Ditemukan Kembali',
                        'Retur Pelanggan Manual' => 'Retur Pelanggan Manual',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required(),

                Textarea::make('note')
                    ->label('Catatan Tambahan (Opsional)')
                    ->placeholder('Keterangan detail teknisi atau kasir...')
                    ->rows(2),

                // Penanganan Serial untuk Direction 'out'
                Select::make('selected_serials')
                    ->label('Pilih Nomor Seri yang Dikeluarkan')
                    ->multiple()
                    ->visible(fn (callable $get) => $record->product->has_sn && $get('direction') === 'out')
                    ->options(fn () => ProductSerial::where('branch_id', $record->branch_id)
                        ->where('product_variant_id', $record->product_variant_id)
                        ->where('status', 'available')
                        ->pluck('serial_number', 'id')
                        ->toArray())
                    ->required(fn (callable $get) => $record->product->has_sn && $get('direction') === 'out')
                    ->helperText(fn (callable $get) => "Pilih persis " . ($get('quantity') ?: 1) . " nomor seri."),

                // Penanganan Serial untuk Direction 'in'
                TagsInput::make('new_serials')
                    ->label('Daftarkan Nomor Seri Baru')
                    ->visible(fn (callable $get) => $record->product->has_sn && $get('direction') === 'in')
                    ->placeholder('Ketik nomor seri lalu tekan Enter')
                    ->required(fn (callable $get) => $record->product->has_sn && $get('direction') === 'in')
                    ->helperText(fn (callable $get) => "Masukkan persis " . ($get('quantity') ?: 1) . " nomor seri."),
            ])
            ->action(function (array $data, Stock $record) {
                try {
                    $service = app(StockAdjustmentService::class);
                    $newStock = $service->adjust(
                        branchId: $record->branch_id,
                        productId: $record->product_id,
                        variantId: $record->product_variant_id,
                        direction: $data['direction'],
                        quantity: (int) $data['quantity'],
                        reason: $data['reason'],
                        note: $data['note'] ?? null,
                        selectedSerialIds: $data['selected_serials'] ?? [],
                        newSerialNumbers: $data['new_serials'] ?? []
                    );

                    $sign = $data['direction'] === 'in' ? '+' : '-';
                    $unit = $record->product->unit->name ?? 'Pcs';

                    Notification::make()
                        ->title('Stok Berhasil Disesuaikan')
                        ->body("Perubahan: {$sign}{$data['quantity']} {$unit}. Saldo stok terkini: {$newStock->stock} {$unit}.")
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Penyesuaian Stok Gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    throw new Halt();
                }
            });
    }

    /**
     * Action untuk Header Tabel StocksTable (menyesuaikan item apa pun dari master katalog).
     */
    public static function makeHeaderAction(string $name = 'adjust_stock_header'): Action
    {
        return Action::make($name)
            ->label('Penyesuaian Stok')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->modalHeading('Penyesuaian Stok Manual')
            ->modalDescription('Tambah atau kurangi saldo stok barang secara insidental.')
            ->modalWidth('lg')
            ->fillForm(fn (): array => [
                'branch_id' => session('current_branch_id') ?: Branch::where('is_active', true)->first()?->id,
                'direction' => 'in',
                'quantity' => 1,
                'reason' => 'Pembelian Kas Eceran',
                'note' => '',
                'selected_serials' => [],
                'new_serials' => [],
            ])
            ->schema([
                Select::make('branch_id')
                    ->label('Cabang')
                    ->options(fn () => Branch::where('is_active', true)->pluck('name', 'id')->toArray())
                    ->default(fn () => session('current_branch_id') ?: Branch::where('is_active', true)->first()?->id)
                    ->required()
                    ->live(),

                Select::make('product_id')
                    ->label('Produk')
                    ->options(fn () => Product::where('type', 'product')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $product = Product::find($state);
                        $defaultVariant = $product?->variants()->first();
                        $set('product_variant_id', $defaultVariant?->id);
                    }),

                Select::make('product_variant_id')
                    ->label('Varian Produk')
                    ->options(function (callable $get) {
                        $productId = $get('product_id');
                        if (!$productId) return [];
                        return ProductVariant::where('product_id', $productId)->pluck('name', 'id')->toArray();
                    })
                    ->visible(function (callable $get) {
                        $productId = $get('product_id');
                        if (!$productId) return false;
                        return ProductVariant::where('product_id', $productId)->whereNotNull('name')->count() > 1;
                    })
                    ->live(),

                Placeholder::make('current_stock_info')
                    ->label('Informasi Stok Saat Ini')
                    ->content(function (callable $get) {
                        $branchId = $get('branch_id');
                        $productId = $get('product_id');
                        $variantId = $get('product_variant_id');

                        if (!$branchId || !$productId) {
                            return 'Silakan pilih cabang dan produk terlebih dahulu.';
                        }

                        $stock = Stock::where('branch_id', $branchId)
                            ->where('product_id', $productId)
                            ->when($variantId, fn ($q) => $q->where('product_variant_id', $variantId))
                            ->value('stock') ?? 0;

                        $product = Product::find($productId);
                        $unit = $product?->unit->name ?? 'Pcs';

                        return new HtmlString("<strong>Saldo Saat Ini:</strong> {$stock} {$unit}");
                    }),

                Radio::make('direction')
                    ->label('Tipe Penyesuaian')
                    ->options([
                        'in'  => 'Tambah / Stok Masuk (+)',
                        'out' => 'Kurang / Stok Keluar (-)',
                    ])
                    ->default('in')
                    ->inline()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => 
                        $set('reason', $state === 'in' ? 'Pembelian Kas Eceran' : 'Barang Rusak / Pecah')
                    ),

                TextInput::make('quantity')
                    ->label('Jumlah (Kuantitas)')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->live(),

                Select::make('reason')
                    ->label('Alasan Penyesuaian')
                    ->options(fn (callable $get) => $get('direction') === 'out' ? [
                        'Barang Rusak / Pecah' => 'Barang Rusak / Pecah',
                        'Barang Hilang / Selisih' => 'Barang Hilang / Selisih',
                        'Kadaluarsa / Tidak Layak Jual' => 'Kadaluarsa / Tidak Layak Jual',
                        'Pemakaian Internal Bengkel' => 'Pemakaian Internal Bengkel',
                        'Koreksi Fisik Kurang' => 'Koreksi Fisik Kurang',
                        'Lainnya' => 'Lainnya',
                    ] : [
                        'Pembelian Kas Eceran' => 'Pembelian Kas Eceran',
                        'Koreksi Fisik Lebih' => 'Koreksi Fisik Lebih',
                        'Barang Ditemukan Kembali' => 'Barang Ditemukan Kembali',
                        'Retur Pelanggan Manual' => 'Retur Pelanggan Manual',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required(),

                Textarea::make('note')
                    ->label('Catatan Tambahan (Opsional)')
                    ->placeholder('Keterangan detail penyesuaian...')
                    ->rows(2),

                // Serial Out
                Select::make('selected_serials')
                    ->label('Pilih Nomor Seri yang Dikeluarkan')
                    ->multiple()
                    ->visible(function (callable $get) {
                        $productId = $get('product_id');
                        $product = Product::find($productId);
                        return $product?->has_sn && $get('direction') === 'out';
                    })
                    ->options(function (callable $get) {
                        $branchId = $get('branch_id');
                        $variantId = $get('product_variant_id');
                        if (!$branchId || !$variantId) return [];

                        return ProductSerial::where('branch_id', $branchId)
                            ->where('product_variant_id', $variantId)
                            ->where('status', 'available')
                            ->pluck('serial_number', 'id')
                            ->toArray();
                    })
                    ->required(function (callable $get) {
                        $productId = $get('product_id');
                        $product = Product::find($productId);
                        return $product?->has_sn && $get('direction') === 'out';
                    }),

                // Serial In
                TagsInput::make('new_serials')
                    ->label('Daftarkan Nomor Seri Baru')
                    ->visible(function (callable $get) {
                        $productId = $get('product_id');
                        $product = Product::find($productId);
                        return $product?->has_sn && $get('direction') === 'in';
                    })
                    ->placeholder('Ketik nomor seri lalu tekan Enter')
                    ->required(function (callable $get) {
                        $productId = $get('product_id');
                        $product = Product::find($productId);
                        return $product?->has_sn && $get('direction') === 'in';
                    }),
            ])
            ->action(function (array $data) {
                try {
                    $service = app(StockAdjustmentService::class);
                    $newStock = $service->adjust(
                        branchId: (int) $data['branch_id'],
                        productId: (int) $data['product_id'],
                        variantId: !empty($data['product_variant_id']) ? (int) $data['product_variant_id'] : null,
                        direction: $data['direction'],
                        quantity: (int) $data['quantity'],
                        reason: $data['reason'],
                        note: $data['note'] ?? null,
                        selectedSerialIds: $data['selected_serials'] ?? [],
                        newSerialNumbers: $data['new_serials'] ?? []
                    );

                    $sign = $data['direction'] === 'in' ? '+' : '-';
                    $product = Product::find($data['product_id']);
                    $unit = $product?->unit->name ?? 'Pcs';

                    Notification::make()
                        ->title('Stok Berhasil Disesuaikan')
                        ->body("Perubahan: {$sign}{$data['quantity']} {$unit}. Saldo stok terkini: {$newStock->stock} {$unit}.")
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Penyesuaian Stok Gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    throw new Halt();
                }
            });
    }
}
