<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterBranchesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('branches')->delete();
        
        \Illuminate\Support\Facades\DB::table('branches')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Pusat',
                'code' => 'KTT',
                'phone' => '081200000001',
                'address' => 'Jl. Contoh Pusat No. 1',
                'latitude' => '-7.25750000',
                'longitude' => '112.75210000',
                'attendance_radius' => 10,
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => '2026-08-05 23:36:56',
                'updated_at' => '2026-08-10 13:26:34',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Cabang Kota',
                'code' => 'STKSM',
                'phone' => '081200000002',
                'address' => 'Jl. Contoh Cabang No. 2',
                'latitude' => '-7.26500000',
                'longitude' => '112.74500000',
                'attendance_radius' => 10,
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => '2026-08-11 13:55:46',
                'updated_at' => '2026-08-11 13:55:46',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Cabang Seeder Utama',
                'code' => 'BR-SEED-01',
                'phone' => '0211111111',
                'address' => 'Jl. Cabang Seeder 1',
                'latitude' => '-7.27000000',
                'longitude' => '112.74000000',
                'attendance_radius' => 50,
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-25 00:02:56',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Cabang Seeder Kedua',
                'code' => 'BR-SEED-02',
                'phone' => '0212222222',
                'address' => 'Jl. Cabang Seeder 2',
                'latitude' => NULL,
                'longitude' => NULL,
                'attendance_radius' => 10,
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
        ));
        
        
    }
}
