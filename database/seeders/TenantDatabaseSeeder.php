<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Models\Tenant\User;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        // Panggil seeder master data dari tabel utama (tenant__schienetren)
        $this->call([
            TenantMasterRolesTableSeeder::class,
            TenantMasterPermissionsTableSeeder::class,
            TenantMasterRoleHasPermissionsTableSeeder::class,
            TenantMasterBranchesTableSeeder::class,
            TenantMasterBrandsTableSeeder::class,
            TenantMasterCategoriesTableSeeder::class,
            TenantMasterDeviceTypesTableSeeder::class, // <-- Dipindah ke atas agar device_type_id tersedia
            TenantMasterChecklistTemplatesTableSeeder::class,
            TenantMasterCompensationComponentsTableSeeder::class,
            TenantMasterCompensationPackagesTableSeeder::class,
            TenantMasterDiscountsTableSeeder::class,
            TenantMasterHolidaysTableSeeder::class,
            TenantMasterLeaveTypesTableSeeder::class,
            TenantMasterPaymentMethodsTableSeeder::class,
            TenantMasterSettingsTableSeeder::class,
            TenantMasterSkillLevelsTableSeeder::class,
            TenantMasterSkillSpecializationsTableSeeder::class,
            TenantMasterTaxesTableSeeder::class,
            TenantMasterUnitsTableSeeder::class,
        ]);

        // Jika owner belum ada dalam role seeder di atas, ini akan membuatnya
        // $superAdmin = Role::create(['name' => 'owner', 'guard_name' => 'tenant']);

        // Ambil ID central user dari tenant yang sedang aktif
        $centralUserId = tenant('user_id'); 
        $centralUser = $centralUserId ? \App\Models\User::find($centralUserId) : null;

        $owner = User::firstOrCreate([
            'email' => $centralUser ? $centralUser->email : 'admin@tenant.com',
        ], [
            'name' => $centralUser ? $centralUser->name : 'Owner Tenant',
            'password' => $centralUser ? $centralUser->password : bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $owner->assignRole('owner');

        $this->call([
            // InventorySeeder dinonaktifkan agar database hanya memuat master data murni (tanpa dummy dummy inventory/transaksi)
            // InventorySeeder::class,

            // =========================================================================
            // Master Data Riil dari DS Komputer (Laptop & Sparepart)
            // =========================================================================
            DsKomputerMasterSeeder::class,
        ]);
    }
}
