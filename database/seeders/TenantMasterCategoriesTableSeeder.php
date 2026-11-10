<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('categories')->delete();
        
        \Illuminate\Support\Facades\DB::table('categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'LCD',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-10 13:09:15',
                'updated_at' => '2026-08-10 13:09:15',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Software',
                'type' => 'service',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-10 13:10:07',
                'updated_at' => '2026-08-10 13:10:30',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Keyboard',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-11 23:43:54',
                'updated_at' => '2026-08-11 23:43:54',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Install Ulang',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-11 23:48:41',
                'updated_at' => '2026-08-11 23:48:41',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'SSD SATA',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-11 23:52:26',
                'updated_at' => '2026-08-11 23:52:26',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Install Aplikasi',
                'type' => 'product',
                'description' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2026-08-12 00:18:54',
                'updated_at' => '2026-08-12 00:18:54',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Elektronik Seeder',
                'type' => 'product',
                'description' => 'Kategori Elektronik dari Seeder',
                'deleted_at' => NULL,
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'Aksesoris Seeder',
                'type' => 'product',
                'description' => 'Kategori Aksesoris dari Seeder',
                'deleted_at' => NULL,
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
        ));
        
        
    }
}
