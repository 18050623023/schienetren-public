<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterBrandsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('brands')->delete();

        \Illuminate\Support\Facades\DB::table('brands')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Asus',
                'type' => 'both',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-09 23:46:37',
                'updated_at' => '2026-08-09 23:46:37',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'MSI',
                'type' => 'device',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-09 23:46:59',
                'updated_at' => '2026-08-09 23:46:59',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Ramsta',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-09 23:47:07',
                'updated_at' => '2026-08-09 23:47:07',
            ),
            3 => 
            array (
                'id' => 5,
                'name' => 'Brand Alpha Seeder',
                'type' => 'product',
                'description' => 'Brand Alpha dari Seeder',
                'deleted_at' => NULL,
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
            4 => 
            array (
                'id' => 6,
                'name' => 'Brand Beta Seeder',
                'type' => 'both',
                'description' => 'Brand Beta dari Seeder',
                'deleted_at' => NULL,
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
            5 => 
            array (
                'id' => 7,
                'name' => 'Acer',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-16 03:16:50',
                'updated_at' => '2026-08-16 03:16:50',
            ),
        ));
    }
}
