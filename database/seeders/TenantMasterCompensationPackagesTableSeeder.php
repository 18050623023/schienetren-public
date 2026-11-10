<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterCompensationPackagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('compensation_packages')->delete();
        
        \Illuminate\Support\Facades\DB::table('compensation_packages')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Teknisi',
                'deleted_at' => NULL,
                'created_at' => '2026-08-15 23:34:08',
                'updated_at' => '2026-08-15 23:34:08',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Teknisi training',
                'deleted_at' => NULL,
                'created_at' => '2026-08-25 00:11:28',
                'updated_at' => '2026-08-25 00:12:19',
            ),
        ));
        
        
    }
}
