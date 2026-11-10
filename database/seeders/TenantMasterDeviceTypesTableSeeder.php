<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterDeviceTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('device_types')->delete();
        
        \Illuminate\Support\Facades\DB::table('device_types')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Laptop',
                'deleted_at' => NULL,
                'created_at' => '2026-08-05 17:14:01',
                'updated_at' => '2026-08-05 17:14:01',
            ),
        ));
        
        
    }
}
