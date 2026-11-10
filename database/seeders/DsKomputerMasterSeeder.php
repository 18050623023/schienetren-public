<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Brand;
use App\Models\Tenant\Category;
use App\Models\Tenant\CompensationComponent;
use App\Models\Tenant\CompensationPackage;
use App\Models\Tenant\DeviceModel;
use App\Models\Tenant\DeviceType;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use App\Models\Tenant\Supplier;
use App\Models\Tenant\Unit;

/**
 * ============================================================================
 * [BARU DITAMBAHKAN] DsKomputerMasterSeeder
 * ============================================================================
 * Seeder tambahan data master riil yang diekstrak dari tenant "dscom" (DS Komputer).
 * 
 * Karakteristik Keamanan:
 * 1. TIDAK MENIMPA / TIDAK MENGHAPUS data seeder lama (tidak ada fungsi delete/truncate).
 * 2. IDEMPOTENT: Menggunakan firstOrCreate() dan updateOrInsert() berdasarkan kode/nama unik,
 *    sehingga aman dijalankan berulang kali tanpa membuat data ganda.
 * 3. Menghubungkan relasi secara dinamis berdasarkan nama/kode entitas.
 */
class DsKomputerMasterSeeder extends Seeder
{
    public function run(): void
    {
        // --------------------------------------------------------------------
        // 1. [BARU DITAMBAHKAN] Master Supplier Riil (Toko Sparepart Laptop)
        // --------------------------------------------------------------------
        $supplier = Supplier::firstOrCreate(
            ['company' => 'Multi Sparepartindo'],
            [
                'name' => 'PT Multi Sparepart Nusantara',
                'whatsapp' => '081233445566',
                'description' => 'Distributor LCD & Sparepart Laptop',
                'address' => 'Komplek Harco Mangga Dua Blok B No. 12, Jakarta',
                'is_active' => true,
            ]
        );

        // --------------------------------------------------------------------
        // 2. [BARU DITAMBAHKAN] Master Brand & Kategori Pelengkap
        // --------------------------------------------------------------------
        $asusBrand = Brand::firstOrCreate(['name' => 'Asus'], ['type' => 'both']);
        $acerBrand = Brand::firstOrCreate(['name' => 'Acer'], ['type' => 'both']);
        $ramstaBrand = Brand::firstOrCreate(['name' => 'Ramsta'], ['type' => 'product']);
        $maxtorBrand = Brand::firstOrCreate(['name' => 'Maxtor'], ['type' => 'product']);
        $innoluxBrand = Brand::firstOrCreate(['name' => 'Innolux'], ['type' => 'product']);

        $laptopType = DeviceType::firstOrCreate(['name' => 'Laptop']);

        $catLcd = Category::firstOrCreate(['name' => 'LCD'], ['type' => 'product']);
        $catKeyboard = Category::firstOrCreate(['name' => 'Keyboard'], ['type' => 'product']);
        $catSsd = Category::firstOrCreate(['name' => 'SSD SATA'], ['type' => 'product']);
        $catThermal = Category::firstOrCreate(['name' => 'Thermal'], ['type' => 'product']);

        $unitPcs = Unit::firstOrCreate(['name' => 'PCS'], ['description' => 'Pieces']);

        // --------------------------------------------------------------------
        // 3. [BARU DITAMBAHKAN] Model Perangkat Laptop Riil
        // --------------------------------------------------------------------
        $modelAsus = DeviceModel::firstOrCreate(
            [
                'device_type_id' => $laptopType->id,
                'brand_id' => $asusBrand->id,
                'name' => 'A414',
            ],
            ['description' => 'Asus Vivobook A414']
        );

        $modelAcer = DeviceModel::firstOrCreate(
            [
                'device_type_id' => $laptopType->id,
                'brand_id' => $acerBrand->id,
                'name' => 'A314 Apire 3',
            ],
            ['description' => 'Acer Aspire 3 A314 Series']
        );

        // --------------------------------------------------------------------
        // 4. [BARU DITAMBAHKAN] Produk Sparepart Riil Beserta Varian & Harga
        // --------------------------------------------------------------------

        // A. SSD SATA Ramsta (Dengan SN Serial Number)
        $ssdProduct = Product::firstOrCreate(
            ['code' => 'ramsta128'],
            [
                'name' => 'SSD SATA',
                'warranty_unit' => 'month',
                'warranty_value' => 3,
                'brand_id' => $ramstaBrand->id,
                'unit_id' => $unitPcs->id,
                'has_sn' => 1,
                'has_variant' => 1,
                'type' => 'product',
            ]
        );
        $ssdProduct->categories()->syncWithoutDetaching([$catSsd->id]);

        ProductVariant::firstOrCreate(
            ['product_id' => $ssdProduct->id, 'sku' => 'ramstasata128'],
            ['name' => '128 GB', 'purchase_price' => 500000, 'sale_price' => 800000]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $ssdProduct->id, 'sku' => 'ramstasata256'],
            ['name' => '256 GB', 'purchase_price' => 800000, 'sale_price' => 1500000]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $ssdProduct->id, 'sku' => 'ramstasata512'],
            ['name' => '512 GB', 'purchase_price' => 1500000, 'sale_price' => 1800000]
        );

        // B. Keyboard Asus A412
        $kbProduct = Product::firstOrCreate(
            ['code' => 'keyboardA412'],
            [
                'name' => 'Keyboard A412',
                'warranty_unit' => 'month',
                'warranty_value' => 1,
                'brand_id' => $asusBrand->id,
                'unit_id' => $unitPcs->id,
                'has_sn' => 0,
                'has_variant' => 1,
                'type' => 'product',
            ]
        );
        $kbProduct->categories()->syncWithoutDetaching([$catKeyboard->id]);

        ProductVariant::firstOrCreate(
            ['product_id' => $kbProduct->id, 'sku' => 'keyboardA145Black'],
            ['name' => 'Black', 'purchase_price' => 300000, 'sale_price' => 800000]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $kbProduct->id, 'sku' => 'keyboardA145'],
            ['name' => 'White', 'purchase_price' => 500000, 'sale_price' => 1000000]
        );

        // C. Thermal Pasta Maxtor
        $pastaProduct = Product::firstOrCreate(
            ['code' => 'thermalmaxtor'],
            [
                'name' => 'Thermal Pasta',
                'warranty_unit' => null,
                'warranty_value' => 0,
                'brand_id' => $maxtorBrand->id,
                'unit_id' => $unitPcs->id,
                'has_sn' => 0,
                'has_variant' => 1,
                'type' => 'product',
            ]
        );
        $pastaProduct->categories()->syncWithoutDetaching([$catThermal->id]);

        ProductVariant::firstOrCreate(
            ['product_id' => $pastaProduct->id, 'sku' => 'maxtorctg8'],
            ['name' => 'CTG 8', 'purchase_price' => 90000, 'sale_price' => 150000]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $pastaProduct->id, 'sku' => 'maxtorctg10'],
            ['name' => 'CTG 10', 'purchase_price' => 100000, 'sale_price' => 160000]
        );

        // D. LCD 14.0 FHD 30-Pin
        $lcdProduct = Product::firstOrCreate(
            ['code' => 'LCD14FHD30p'],
            [
                'name' => 'LCD 14.0 FHD 30-pin',
                'warranty_unit' => 'month',
                'warranty_value' => 3,
                'brand_id' => $innoluxBrand->id,
                'unit_id' => $unitPcs->id,
                'has_sn' => 0,
                'has_variant' => 1,
                'type' => 'product',
            ]
        );
        $lcdProduct->categories()->syncWithoutDetaching([$catLcd->id]);
        $lcdProduct->compatibleModels()->syncWithoutDetaching([$modelAsus->id, $modelAcer->id]);

        ProductVariant::firstOrCreate(
            ['product_id' => $lcdProduct->id, 'sku' => 'LCD14FHD30poem'],
            ['name' => 'OEM', 'purchase_price' => 100000, 'sale_price' => 300000]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $lcdProduct->id, 'sku' => 'LCD14FHD30pbekas'],
            ['name' => 'Ori Copotan', 'purchase_price' => 80000, 'sale_price' => 250000]
        );

        // --------------------------------------------------------------------
        // 5. [BARU DITAMBAHKAN] Skema Kompensasi & Gaji Teknisi Riil
        // --------------------------------------------------------------------
        $compGaji = CompensationComponent::firstOrCreate(
            ['name' => 'Gaji Pokok'],
            ['type' => 'earning', 'is_active' => true]
        );
        $compBonus = CompensationComponent::firstOrCreate(
            ['name' => 'Bonus Service'],
            ['type' => 'earning', 'is_active' => true]
        );

        $pkgTeknisi = CompensationPackage::firstOrCreate(
            ['name' => 'Teknisi']
        );

        DB::table('compensation_package_components')->updateOrInsert(
            [
                'compensation_package_id' => $pkgTeknisi->id,
                'compensation_component_id' => $compGaji->id,
            ],
            [
                'calc_type' => 'fixed',
                'value' => 4000000.00,
                'based_on' => 'basic_salary',
                'updated_at' => now(),
            ]
        );

        DB::table('compensation_package_components')->updateOrInsert(
            [
                'compensation_package_id' => $pkgTeknisi->id,
                'compensation_component_id' => $compBonus->id,
            ],
            [
                'calc_type' => 'percentage',
                'value' => 5.00,
                'based_on' => 'repair_item',
                'updated_at' => now(),
            ]
        );
    }
}
