<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Category;
use App\Models\Tenant\Brand;
use App\Models\Tenant\Unit;
use App\Models\Tenant\Supplier;
use App\Models\Tenant\Branch;
use App\Models\Tenant\User;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockOpnameItem;
use App\Models\Tenant\StockOpnameItemSerial;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories (2 data)
        $category1 = Category::firstOrCreate(
            ['name' => 'Elektronik Seeder'],
            [
                'type' => 'product',
                'description' => 'Kategori Elektronik dari Seeder'
            ]
        );

        $category2 = Category::firstOrCreate(
            ['name' => 'Aksesoris Seeder'],
            [
                'type' => 'product',
                'description' => 'Kategori Aksesoris dari Seeder'
            ]
        );

        // 2. Brands (2 data)
        $brand1 = Brand::firstOrCreate(
            ['name' => 'Brand Alpha Seeder'],
            [
                'type' => 'product',
                'description' => 'Brand Alpha dari Seeder'
            ]
        );

        $brand2 = Brand::firstOrCreate(
            ['name' => 'Brand Beta Seeder'],
            [
                'type' => 'both',
                'description' => 'Brand Beta dari Seeder'
            ]
        );

        // 3. Units (2 data)
        $unit1 = Unit::firstOrCreate(
            ['name' => 'Pcs Seeder'],
            ['description' => 'Satuan Pcs dari Seeder']
        );

        $unit2 = Unit::firstOrCreate(
            ['name' => 'Box Seeder'],
            ['description' => 'Satuan Box dari Seeder']
        );

        // 4. Suppliers (2 data)
        $supplier1 = Supplier::firstOrCreate(
            ['name' => 'Supplier Utama Seeder'],
            [
                'company' => 'PT Utama Seeder Jaya',
                'whatsapp' => '081234567890',
                'description' => 'Supplier Utama dari Seeder',
                'address' => 'Jl. Seeder No. 1'
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['name' => 'Supplier Resmi Seeder'],
            [
                'company' => 'CV Resmi Seeder Abadi',
                'whatsapp' => '089876543210',
                'description' => 'Supplier Resmi dari Seeder',
                'address' => 'Jl. Seeder No. 2'
            ]
        );

        // 5. Branches (2 data)
        $branch1 = Branch::firstOrCreate(
            ['code' => 'BR-SEED-01'],
            [
                'name' => 'Cabang Seeder Utama',
                'phone' => '0211111111',
                'address' => 'Jl. Cabang Seeder 1',
                'is_active' => true,
            ]
        );

        $branch2 = Branch::firstOrCreate(
            ['code' => 'BR-SEED-02'],
            [
                'name' => 'Cabang Seeder Kedua',
                'phone' => '0212222222',
                'address' => 'Jl. Cabang Seeder 2',
                'is_active' => true,
            ]
        );

        // Get user for fallback reference
        $user = User::first() ?? User::firstOrCreate([
            'name' => 'Seeder System User',
            'email' => 'seeder@system.local',
            'password' => bcrypt('password'),
        ]);

        // 6. Products (2 data)
        $product1 = Product::firstOrCreate(
            ['code' => 'PRD-SEED-001'],
            [
                'name' => 'Smartphone Seeder Pro',
                'warranty_unit' => 'month',
                'warranty_value' => 12,
                'description' => 'Produk Smartphone Seeder Pro',
                'brand_id' => $brand1->id,
                'unit_id' => $unit1->id,
                'has_sn' => true,
                'has_variant' => true,
                'is_include_service' => false,
                'type' => 'product',
            ]
        );
        $product1->categories()->syncWithoutDetaching([$category1->id]);

        $product2 = Product::firstOrCreate(
            ['code' => 'PRD-SEED-002'],
            [
                'name' => 'Kabel Data Seeder Fast',
                'warranty_unit' => 'month',
                'warranty_value' => 6,
                'description' => 'Produk Aksesoris Kabel Data Seeder',
                'brand_id' => $brand2->id,
                'unit_id' => $unit2->id,
                'has_sn' => false,
                'has_variant' => true,
                'is_include_service' => false,
                'type' => 'product',
            ]
        );
        $product2->categories()->syncWithoutDetaching([$category2->id]);

        // 7. Product Variants (2 data)
        $variant1 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SEED-001'],
            [
                'product_id' => $product1->id,
                'name' => 'Black 128GB',
                'purchase_price' => 3000000,
                'sale_price' => 3500000,
            ]
        );

        $variant2 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SEED-002'],
            [
                'product_id' => $product2->id,
                'name' => 'Type-C 1 Meter',
                'purchase_price' => 25000,
                'sale_price' => 50000,
            ]
        );

        // 8. Product Serials (2 data)
        $serial1 = ProductSerial::firstOrCreate(
            ['serial_number' => 'SN-SEED-00001'],
            [
                'branch_id' => $branch1->id,
                'product_variant_id' => $variant1->id,
                'status' => 'available',
            ]
        );

        $serial2 = ProductSerial::firstOrCreate(
            ['serial_number' => 'SN-SEED-00002'],
            [
                'branch_id' => $branch2->id,
                'product_variant_id' => $variant1->id,
                'status' => 'available',
            ]
        );

        // 9. Stocks (2 data)
        $stock1 = Stock::firstOrCreate(
            [
                'branch_id' => $branch1->id,
                'product_id' => $product1->id,
                'product_variant_id' => $variant1->id,
            ],
            [
                'stock' => 50,
            ]
        );

        $stock2 = Stock::firstOrCreate(
            [
                'branch_id' => $branch2->id,
                'product_id' => $product2->id,
                'product_variant_id' => $variant2->id,
            ],
            [
                'stock' => 100,
            ]
        );

        // 10. Stock Movements (2 data)
        $movement1 = StockMovement::firstOrCreate(
            [
                'branch_id' => $branch1->id,
                'product_id' => $product1->id,
                'product_variant_id' => $variant1->id,
                'note' => 'Stok Awal Seeder #1',
            ],
            [
                'direction' => 'in',
                'quantity' => 50,
                'source_type' => Product::class,
                'source_id' => $product1->id,
            ]
        );

        $movement2 = StockMovement::firstOrCreate(
            [
                'branch_id' => $branch2->id,
                'product_id' => $product2->id,
                'product_variant_id' => $variant2->id,
                'note' => 'Stok Awal Seeder #2',
            ],
            [
                'direction' => 'in',
                'quantity' => 100,
                'source_type' => Product::class,
                'source_id' => $product2->id,
            ]
        );

        // 11. Stock Opnames (2 data)
        $opname1 = StockOpname::firstOrCreate(
            ['reference' => 'SO-SEED-2026-001'],
            [
                'branch_id' => $branch1->id,
                'date' => now()->toDateString(),
                'notes' => 'Stock Opname Seeder Dokumen 1',
                'type' => 'regular',
                'status' => 'draft',
                'created_by' => $user->id,
            ]
        );

        $opname2 = StockOpname::firstOrCreate(
            ['reference' => 'SO-SEED-2026-002'],
            [
                'branch_id' => $branch2->id,
                'date' => now()->toDateString(),
                'notes' => 'Stock Opname Seeder Dokumen 2',
                'type' => 'regular',
                'status' => 'draft',
                'created_by' => $user->id,
            ]
        );

        // 12. Stock Opname Items (2 data)
        $opnameItem1 = StockOpnameItem::firstOrCreate(
            [
                'stock_opname_id' => $opname1->id,
                'product_id' => $product1->id,
                'product_variant_id' => $variant1->id,
            ],
            [
                'branch_id' => $branch1->id,
                'system_qty' => 50,
                'actual_qty' => 50,
                'difference' => 0,
            ]
        );

        $opnameItem2 = StockOpnameItem::firstOrCreate(
            [
                'stock_opname_id' => $opname2->id,
                'product_id' => $product2->id,
                'product_variant_id' => $variant2->id,
            ],
            [
                'branch_id' => $branch2->id,
                'system_qty' => 100,
                'actual_qty' => 98,
                'difference' => -2,
            ]
        );

        // 13. Stock Opname Item Serials (2 data)
        $opnameItemSerial1 = StockOpnameItemSerial::firstOrCreate(
            [
                'stock_opname_item_id' => $opnameItem1->id,
                'serial_number' => 'SN-SEED-00001',
            ],
            [
                'product_id' => $product1->id,
                'product_variant_id' => $variant1->id,
                'product_serial_id' => $serial1->id,
                'status' => 'OK',
                'note' => 'Serial fisik sesuai',
            ]
        );

        $opnameItemSerial2 = StockOpnameItemSerial::firstOrCreate(
            [
                'stock_opname_item_id' => $opnameItem1->id,
                'serial_number' => 'SN-SEED-00002',
            ],
            [
                'product_id' => $product1->id,
                'product_variant_id' => $variant1->id,
                'product_serial_id' => $serial2->id,
                'status' => 'OK',
                'note' => 'Serial fisik sesuai',
            ]
        );
    }
}
