<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Category;
use App\Models\Tenant\Client;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\Stock;
use App\Services\Pos\PosCheckoutService;
use App\Enums\TenantFeature;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PosPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
    protected static ?string $title = 'Point of Sale (POS)';
    protected static ?string $navigationLabel = 'Point of Sale (POS)';
    protected static ?string $slug = 'pos';
    protected static string|UnitEnum|null $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.tenant.pages.pos-page';

    public function getHeading(): string
    {
        return '';
    }

    // State
    public ?int $branchId = null;
    public string $search = '';
    public ?int $selectedCategoryId = null;
    public ?int $selectedClientId = null;
    
    // Cart: array of items
    public array $cart = [];

    // Global adjustments
    public string $discountType = 'fixed'; // 'fixed' | 'percentage'
    public float $discountValue = 0;
    public float $taxAmount = 0;

    // Checkout modal
    public bool $showPaymentModal = false;
    public ?int $paymentMethodId = null;
    public float $paidAmount = 0;
    public string $notes = '';

    // Success / Print modal
    public bool $showSuccessModal = false;
    public ?int $lastSaleId = null;
    public ?string $lastInvoiceNumber = null;
    public float $lastGrandTotal = 0;
    public float $lastChangeAmount = 0;

    // New Client Quick Add Modal
    public bool $showNewClientModal = false;
    public string $newClientName = '';
    public string $newClientPhone = '';

    public static function canAccess(): bool
    {
        if (!tenant()->hasFeatureCode(TenantFeature::POS)) {
            return false;
        }

        $user = Auth::user();
        if (!$user) return false;

        $bypassRoles = settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']);
        return $user->hasRole($bypassRoles)
            || $user->hasRole(['cashier', 'customer_service', 'cs', 'manager'])
            || $user->can('access_pos');
    }

    public function mount(): void
    {
        $this->branchId = session('current_branch_id') ?: Branch::first()?->id;

        // Default payment method
        $defaultMethod = PaymentMethod::where('is_active', true)->first();
        if ($defaultMethod) {
            $this->paymentMethodId = $defaultMethod->id;
        }
    }

    public function updatedBranchId(): void
    {
        $this->clearCart();
    }

    public function updatedSearch(): void
    {
        $query = trim($this->search);
        if (empty($query)) return;

        // 1. Cek apakah ini Barcode Serial Number yang unik
        $serial = ProductSerial::where('branch_id', $this->branchId)
            ->where('serial_number', $query)
            ->where('status', 'available')
            ->first();

        if ($serial) {
            foreach ($this->cart as $line) {
                if (in_array($serial->id, $line['selected_serials'] ?? [])) {
                    Notification::make()
                        ->title("Nomor seri '{$serial->serial_number}' sudah ada di dalam keranjang")
                        ->warning()
                        ->send();
                    $this->search = '';
                    return;
                }
            }

            $this->addToCart($serial->product_variant_id, 1, $serial->id);
            $this->search = '';
            Notification::make()
                ->title('Produk serial berhasil ditambahkan ke keranjang')
                ->success()
                ->send();
            return;
        }

        // 2. Cek apakah ini Barcode/SKU Varian tepat
        $variant = ProductVariant::where('sku', $query)->first();
        if ($variant) {
            $this->addToCart($variant->id, 1);
            $this->search = '';
            Notification::make()
                ->title('Produk SKU berhasil ditambahkan ke keranjang')
                ->success()
                ->send();
        }
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = ($this->selectedCategoryId === $categoryId) ? null : $categoryId;
    }

    public function getAvailableProductsProperty()
    {
        if (!$this->branchId) {
            return collect();
        }

        $query = ProductVariant::query()
            ->with(['product.brand', 'product.categories', 'stocks' => function ($q) {
                $q->where('branch_id', $this->branchId);
            }])
            ->whereHas('product', function ($q) {
                $q->whereNull('deleted_at')
                    ->where('type', 'product');
            });

        if ($this->selectedCategoryId) {
            $query->whereHas('product.categories', function ($q) {
                $q->where('categories.id', $this->selectedCategoryId);
            });
        }

        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('sku', 'like', $term)
                  ->orWhereHas('product', function ($pq) use ($term) {
                      $pq->where('name', 'like', $term)
                         ->orWhere('code', 'like', $term);
                  });
            });
        }

        return $query->take(30)->get()->map(function ($variant) {
            $branchStock = $variant->stocks->firstWhere('branch_id', $this->branchId);
            $variant->current_branch_stock = $branchStock ? (int) $branchStock->stock : 0;
            return $variant;
        });
    }

    public function addToCart(int $variantId, int $qty = 1, ?int $serialId = null): void
    {
        if (!$this->branchId) {
            Notification::make()->title('Silakan pilih cabang terlebih dahulu')->warning()->send();
            return;
        }

        $variant = ProductVariant::with(['product'])->findOrFail($variantId);
        if ($variant->product?->type !== 'product') {
            Notification::make()->title('Produk tipe jasa tidak dapat dijual di kasir POS')->warning()->send();
            return;
        }
        $stockModel = Stock::where('branch_id', $this->branchId)
            ->where('product_variant_id', $variantId)
            ->first();

        $currentStock = $stockModel ? (int) $stockModel->stock : 0;
        $hasSerial = (bool) ($variant->product?->has_sn ?? false);

        // Hitung total kuantitas varian ini yang sudah ada di keranjang (baik di baris yang sama maupun baris terpisah)
        $currentCartQty = 0;
        $existingIndex = null;
        foreach ($this->cart as $idx => $line) {
            if ($line['variant_id'] === $variantId) {
                $currentCartQty += $line['qty'];
                if (!$hasSerial && $existingIndex === null) {
                    $existingIndex = $idx;
                }
            }
        }

        $effectiveAddQty = $hasSerial ? 1 : $qty;
        $newTotalQty = $currentCartQty + $effectiveAddQty;

        if ($newTotalQty > $currentStock) {
            Notification::make()
                ->title("Stok tidak mencukupi. Sisa stok di cabang: {$currentStock}")
                ->danger()
                ->send();
            return;
        }

        $availableSerials = [];
        if ($hasSerial) {
            $availableSerials = ProductSerial::where('branch_id', $this->branchId)
                ->where('product_variant_id', $variantId)
                ->where('status', 'available')
                ->pluck('serial_number', 'id')
                ->toArray();
        }

        // Jika bukan produk serial dan sudah ada di keranjang, gabungkan (update baris yang sudah ada)
        if (!$hasSerial && $existingIndex !== null) {
            $lineQty = $this->cart[$existingIndex]['qty'] + $qty;
            $this->cart[$existingIndex]['qty'] = $lineQty;
            $this->cart[$existingIndex]['subtotal'] = ($this->cart[$existingIndex]['unit_price'] * $lineQty) - $this->cart[$existingIndex]['discount_amount'];
            if ($serialId && !in_array($serialId, $this->cart[$existingIndex]['selected_serials'])) {
                $this->cart[$existingIndex]['selected_serials'][] = $serialId;
            }
        } else {
            // Jika produk serial (has_sn = true), SELALU buat baris baru di keranjang (menumpuk) dengan qty = 1
            $unitPrice = (float) $variant->sale_price;
            $prodName = $variant->product?->name ?? 'Produk';
            $varName = $variant->name ? " ({$variant->name})" : '';

            $this->cart[] = [
                'variant_id' => $variantId,
                'product_id' => $variant->product_id,
                'name' => "{$prodName}{$varName}",
                'sku' => $variant->sku ?: '-',
                'unit_price' => $unitPrice,
                'qty' => $hasSerial ? 1 : $qty,
                'max_stock' => $currentStock,
                'discount_amount' => 0,
                'subtotal' => $unitPrice * ($hasSerial ? 1 : $qty),
                'warranty_days' => (int) ($variant->product?->warranty_value ?? 0),
                'has_serial' => $hasSerial,
                'available_serials' => $availableSerials,
                'selected_serials' => $serialId ? [$serialId] : [],
            ];
        }
    }

    public function updateQty(int $index, int $qty): void
    {
        if (!isset($this->cart[$index])) return;

        // Produk serial selalu tetap 1 unit per baris
        if (!empty($this->cart[$index]['has_serial'])) {
            return;
        }

        if ($qty <= 0) {
            $this->removeFromCart($index);
            return;
        }

        if ($qty > $this->cart[$index]['max_stock']) {
            Notification::make()
                ->title("Maksimal stok tercapai ({$this->cart[$index]['max_stock']})")
                ->warning()
                ->send();
            $this->cart[$index]['qty'] = $this->cart[$index]['max_stock'];
        } else {
            $this->cart[$index]['qty'] = $qty;
        }

        $this->cart[$index]['subtotal'] = ($this->cart[$index]['unit_price'] * $this->cart[$index]['qty']) - $this->cart[$index]['discount_amount'];
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountValue = 0;
        $this->taxAmount = 0;
        $this->paidAmount = 0;
    }

    public function getSubtotalProperty(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getDiscountAmountProperty(): float
    {
        $sub = $this->subtotal;
        if ($this->discountType === 'percentage') {
            return ($sub * ($this->discountValue / 100));
        }
        return min($sub, $this->discountValue);
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, ($this->subtotal - $this->discountAmount) + $this->taxAmount);
    }

    public function getChangeAmountProperty(): float
    {
        return max(0, $this->paidAmount - $this->grandTotal);
    }

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang belanja masih kosong')->warning()->send();
            return;
        }

        $this->paidAmount = $this->grandTotal;
        $this->showPaymentModal = true;
        $this->dispatch('open-modal', id: 'pos-payment-modal');
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->dispatch('close-modal', id: 'pos-payment-modal');
    }

    public function setPaidPreset(float $amount): void
    {
        $this->paidAmount = $amount;
    }

    public function addPaidPreset(float $add): void
    {
        $this->paidAmount += $add;
    }

    public function openNewClientModal(): void
    {
        $this->newClientName = '';
        $this->newClientPhone = '';
        $this->showNewClientModal = true;
        $this->dispatch('open-modal', id: 'pos-client-modal');
    }

    public function closeNewClientModal(): void
    {
        $this->showNewClientModal = false;
        $this->dispatch('close-modal', id: 'pos-client-modal');
    }

    public function quickCreateClient(): void
    {
        $name = trim($this->newClientName);
        if (empty($name)) {
            Notification::make()->title('Nama pelanggan wajib diisi')->warning()->send();
            return;
        }

        $client = Client::create([
            'name' => $name,
            'phone' => trim($this->newClientPhone) ?: null,
            'whatsapp' => trim($this->newClientPhone) ?: null,
        ]);

        $this->selectedClientId = $client->id;
        $this->newClientName = '';
        $this->newClientPhone = '';
        $this->showNewClientModal = false;
        $this->dispatch('close-modal', id: 'pos-client-modal');

        Notification::make()->title("Pelanggan '{$client->name}' berhasil ditambahkan")->success()->send();
    }

    public function processCheckout(PosCheckoutService $checkoutService): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang belanja kosong')->danger()->send();
            return;
        }

        if ($this->paidAmount < $this->grandTotal) {
            Notification::make()->title('Nominal pembayaran kurang dari total tagihan')->danger()->send();
            return;
        }

        foreach ($this->cart as $line) {
            if (!empty($line['has_serial']) && empty($line['selected_serials'][0])) {
                Notification::make()
                    ->title("Silakan pilih nomor seri/IMEI untuk item \"{$line['name']}\" terlebih dahulu")
                    ->danger()
                    ->send();
                return;
            }
        }

        try {
            $itemsPayload = [];
            foreach ($this->cart as $line) {
                $itemsPayload[] = [
                    'product_id' => $line['product_id'],
                    'product_variant_id' => $line['variant_id'],
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => $line['discount_amount'],
                    'warranty_days' => $line['warranty_days'],
                    'serials' => $line['selected_serials'] ?? [],
                ];
            }

            $sale = $checkoutService->checkout([
                'branch_id' => $this->branchId,
                'cashier_id' => Auth::id(),
                'client_id' => $this->selectedClientId,
                'payment_method_id' => $this->paymentMethodId,
                'discount_type' => $this->discountType,
                'discount_value' => $this->discountValue,
                'tax_amount' => $this->taxAmount,
                'paid_amount' => $this->paidAmount,
                'notes' => $this->notes,
                'items' => $itemsPayload,
            ]);

            $this->lastSaleId = $sale->id;
            $this->lastInvoiceNumber = $sale->invoice_number;
            $this->lastGrandTotal = (float) $sale->grand_total;
            $this->lastChangeAmount = (float) $sale->change_amount;

            $this->showPaymentModal = false;
            $this->showSuccessModal = true;
            $this->dispatch('close-modal', id: 'pos-payment-modal');
            $this->dispatch('open-modal', id: 'pos-success-modal');
            $this->clearCart();

            Notification::make()
                ->title("Transaksi #{$sale->invoice_number} Berhasil!")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memproses transaksi: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
