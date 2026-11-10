<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterUnitsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('units')->delete();
        
        \Illuminate\Support\Facades\DB::table('units')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'PCS',
                'description' => NULL,
                'created_at' => '2026-08-11 23:43:43',
                'updated_at' => '2026-08-11 23:43:43',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Pcs Seeder',
                'description' => 'Satuan Pcs dari Seeder',
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Box Seeder',
                'description' => 'Satuan Box dari Seeder',
                'created_at' => '2026-08-14 15:20:42',
                'updated_at' => '2026-08-14 15:20:42',
            ),
        ));
        
        
    }
}
