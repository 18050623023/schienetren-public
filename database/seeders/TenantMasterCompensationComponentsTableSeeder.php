<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterCompensationComponentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('compensation_components')->delete();
        
        \Illuminate\Support\Facades\DB::table('compensation_components')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => '2026-08-25 00:07:00',
                'updated_at' => '2026-08-25 00:07:00',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Bonus Service',
                'type' => 'earning',
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => '2026-08-25 00:08:51',
                'updated_at' => '2026-08-25 00:08:51',
            ),
        ));
        
        
    }
}
